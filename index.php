<?php
session_start();

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_results = [];

if ($search_query) {
    require_once 'db_connection.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM events WHERE title LIKE :search OR location LIKE :search OR description LIKE :search ORDER BY event_date");
    $stmt->bindValue(':search', '%' . $search_query . '%', PDO::PARAM_STR);
    $stmt->execute();
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=0.8" />
    <title>TicketGeek</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>

    <header class="main-header">
        <div class="header-content">
            <div class="left-nav">
                <a href="index.php" class="logo">TicketGeek</a>

                <nav>
                    <a href="concerts.php">Concerts</a>
                    <a href="sports.php">Sports</a>
                    <a href="arts_theatre.php">Arts & Theatre</a>
                    <a href="more.php">More</a>
                </nav>
            </div>

            <div class="right-nav">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="user-icon">
                        <?php echo 'Hello, ' . htmlspecialchars($_SESSION["name"]); ?>
                    </span>
                    
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin_panel.php" class="user-icon" style="margin-left: 10px; color: #ffcc00;">Admin Panel</a>
                    <?php endif; ?>

                    <a href="logout.php" class="user-icon" style="margin-left: 10px; font-size: 0.9em;">Logout</a>
                <?php else: ?>
                    <a href="Login.php" class="user-icon">Login / Sign Up</a>
                <?php endif; ?>
            </div>

            <div class="hamburger" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Overlay -->
    <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleMobileMenu()">
        <div class="mobile-nav-links" onclick="event.stopPropagation()">
            <a href="index.php" class="logo mobile-only" style="font-size: 28px; margin-bottom: 20px;">TicketGeek</a>
            <a href="concerts.php">Concerts</a>
            <a href="sports.php">Sports</a>
            <a href="arts_theatre.php">Arts & Theatre</a>
            <a href="more.php">More</a>
            <hr style="width: 50%; border-color: #444; margin: 15px 0;">
            <?php if(isset($_SESSION['user_id'])): ?>
                <span class="user-icon mobile-only">
                    <?php echo 'Hello, ' . htmlspecialchars($_SESSION["name"]); ?>
                </span>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_panel.php" class="user-icon mobile-only" style="color: #ffcc00;">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="user-icon mobile-only">Logout</a>
            <?php else: ?>
                <a href="Login.php" class="user-icon">Login / Sign Up</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    
    function toggleMobileMenu() {
        const hamburger = document.querySelector('.hamburger');
        const overlay = document.getElementById('mobileNavOverlay');
        hamburger.classList.toggle('active');
        overlay.classList.toggle('active');
        if (overlay.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            document.body.style.overflowX = 'hidden';
        } else {
            document.body.style.overflow = '';
            document.body.style.overflowX = '';
        }
    }
    </script>

    <div class="content-wrapper">
        <section class="hero">
            <h2>Your Next Experience Awaits</h2>
            <p>Discover concerts, sports, and theatre events happening near you.</p>
            <form action="index.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search for artists, teams or events..." class="search-box" value="<?php echo htmlspecialchars($search_query); ?>" />
            </form>
        </section>

        <?php if ($search_query): ?>
        <section class="main-layout" style="grid-template-columns: 1fr;">
        <div class="left-genres">
            <div class="event-row">
                <h2 class="section-title">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
                <?php if (empty($search_results)): ?>
                    <p>No events found matching your search.</p>
                <?php else: ?>
                <div class="event-list" style="flex-wrap: wrap; overflow: visible;">
                    <?php foreach ($search_results as $event): ?>
                    <a href="event.php?id=<?php echo $event['id']; ?>" class="event-card">
                        <div class="event-card-media">
                            <img class="event-card-img" src="<?php echo !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : 'https://picsum.photos/id/240/300/200'; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>"/>
                            <span class="buy-button-overlay">Buy Tickets</span>
                        </div>
                        <div class="event-card-info">
                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                            <p><?php echo date('d M Y', strtotime($event['event_date'])); ?> | <?php echo htmlspecialchars($event['location']); ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        </section>
        <?php else: ?>
        <section class="main-layout">

<div class="left-genres">
    
    <div class="event-row">
        <h2 class="section-title">Concerts Near You</h2>
        <div class="event-list">
            
            <a href="event.php?id=1" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/E0KGzAY312IZShLxDPihble3g-fXSvvRqU0rXnfl_W0/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5nZXR0eWltYWdl/cy5jb20vaWQvMjA1/MjkyOTQ3Ny9waG90/by9zaW5nYXBvcmUt/c2luZ2Fwb3JlLXRh/eWxvci1zd2lmdC1w/ZXJmb3Jtcy1kdXJp/bmctdGF5bG9yLXN3/aWZ0LXRoZS1lcmFz/LXRvdXItYXQtdGhl/LW5hdGlvbmFsLmpw/Zz9zPTYxMng2MTIm/dz0wJms9MjAmYz04/V0RES3ZXejdKVGFv/NV9wVlpyc2NiWlJj/VWh4T2NHUmNDTGVN/ZXF1QzRrPQ" alt="Taylor Swift Concert"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>Taylor Swift - Eras Tour</h4>
                    <p>15 Aug 2026 | London</p>
                </div>
            </a>
            
            <a href="event.php?id=2" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/G08_YeZclFcxXRW-ymF3qfT6dGxEAJcpfJ9O-xh5NvA/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5waXRjaGZvcmsu/Y29tL3Bob3Rvcy82/NzY3MWJlZjU5N2E0/NzA4NTE1MTBiM2Qv/MTY6OS93XzgwMCxo/XzQ1MCxjX2xpbWl0/L1RoZS1XZWVrbmQu/anBlZw" alt="The Weeknd Concert"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>The Weeknd</h4>
                    <p>20 Sep 2026 | Paris</p>
                </div>
            </a>
            
            <a href="event.php?id=3" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/PQVwwwg602QRPZ_aMyxizYpVIIsKKU6aGiwnEDyZc3k/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5nZXR0eWltYWdl/cy5jb20vaWQvMjE2/NjIzNjY5MS9waG90/by9sb25kb24tZW5n/bGFuZC1lZC1zaGVl/cmFuLXBlcmZvcm1z/LW9uc3RhZ2Utd2l0/aC10YXlsb3Itc3dp/ZnQtZHVyaW5nLXRh/eWxvci1zd2lmdC10/aGUtZXJhcy5qcGc_/cz02MTJ4NjEyJnc9/MCZrPTIwJmM9Z243/OTJhLVNZZHJKVFR6/OEVvdm5FRzVYWkRu/RDRzVDJONXEzYk9D/SE50UT0" alt="Ed Sheeran Concert"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>Ed Sheeran Acoustic</h4>
                    <p>5 Oct 2026 | Berlin</p>
                </div>
            </a>
            
            <a href="event.php?id=4" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/LAkwd5PPwj8gqBKG1mbsaqrqJDHYwxUGDCbFtN1eiqs/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5nZXR0eWltYWdl/cy5jb20vaWQvMTAy/MzcwMjE2Mi9waG90/by9uZXctb3JsZWFu/cy1sYS1uYmEteW91/bmdib3ktcGVyZm9y/bXMtZHVyaW5nLWxp/bC13ZWV6eWFuYS1h/dC1jaGFtcGlvbnMt/c3F1YXJlLW9uLWF1/Z3VzdC0yNS5qcGc_/cz02MTJ4NjEyJnc9/MCZrPTIwJmM9dlk1/dTl6c21aNUEydG1p/bTdPRzRadTBIdm5x/SFQyWS1fNFhtdVpH/a3BmQT0" alt="Queen + Adam Lambert Show"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>NBA Youngboy</h4>
                    <p>12 Nov 2026 | Rome</p>
                </div>
            </a>
            
            <a href="event.php?id=5" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/gsf8aThvBcGRiIq3kBU828AadFF2NJfjGJEhTchCEV8/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly93YWxs/cGFwZXJjYXZlLmNv/bS93cC93cDE5OTE4/MDIuanBn" alt="Imagine Dragons Show"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>Imagine Dragons</h4>
                    <p>1 Jan 2027 | Amsterdam</p>
                </div>
            </a>
        </div>
    </div>

    <div class="event-row">
        <h2 class="section-title">Top Sporting Events</h2>
        <div class="event-list">
            
            <a href="event.php?id=6" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/3twds3rO_LjFPXAMrHB_kyAQ2O7yQE1TyAGedUUz5NQ/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5nZXR0eWltYWdl/cy5jb20vaWQvMTE3/MjM3OTExMS9waG90/by93aXRoLXRlYXJz/LWluLWhpcy1leWVz/LWNsZXZlbGFuZC1j/YXZhbGllcnMtbGVi/cm9uLWphbWVzLWhv/bGRzLXVwLXRoZS1t/dnAtdHJvcGh5LWFm/dGVyLmpwZz9zPTYx/Mng2MTImdz0wJms9/MjAmYz05eDItOHFX/eEhheU84RE1fdW5G/Nm9aX2trSmhheE8z/dExSQ0hIYVVpM2w0/PQ" alt="NBA Game"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>NBA Global Games</h4>
                    <p>10 Mar | London</p>
                </div>
            </a>
            
            <a href="event.php?id=7" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/MhsivNyhMobtdKuTXw93vXRW833mehlbjE7Or8J6Ej4/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pLnBp/bmltZy5jb20vb3Jp/Z2luYWxzLzU4LzAy/LzlkLzU4MDI5ZDcy/MDMwNjdlMzRhOWRi/MDkxODQ4ZjEwMzRk/LmpwZw" alt="F1 Grand Prix"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>Formula 1 Grand Prix</h4>
                    <p>17 Apr | Monaco</p>
                </div>
            </a>
            
            <a href="event.php?id=8" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/Q0cr3_rYX7yRukvppEJw-oWqbZcMDoYeY8Q5_cJdNXw/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly93YWxs/cGFwZXJzLmNvbS9p/bWFnZXMvaGQvdWVm/YS1jaGFtcGlvbnMt/bGVhZ3VlLWV1cm9w/ZWFuLWZvb3RiYWxs/LXRyb3BoeS0xYWR0/ZTlmbGIwajZkaHJ3/LmpwZw" alt="Champions League Final"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>Champions League Final</h4>
                    <p>28 May | Istanbul</p>
                </div>
            </a>
            
            <a href="event.php?id=9" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/Ya5aEqnP7T0reyc5RbSGCdt3ayFTtKLknIKZ_YT0Zm0/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zdGF0/aWMuaW5kZXBlbmRl/bnQuY28udWsvMjAy/Mi8wNS8yMC8xOS9H/ZXR0eUltYWdlcy0x/MzI4MTY4MjE2Lmpw/Zz9xdWFsaXR5PTc1/JndpZHRoPTY0MCZj/cm9wPTM6MixzbWFy/dCZhdXRvPXdlYnA" alt="Wimbledon Tennis"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>Wimbledon</h4>
                    <p>5 Jul | London</p>
                </div>
            </a>
        </div>
    </div>
    
    <div class="event-row">
        <h2 class="section-title">Arts & Theatre</h2>
        <div class="event-list">
            
            <a href="event.php?id=10" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/YoODzwZx9A3RcWwSbGO13Ws6pNw8MRcK-XPNAg99d_I/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly90My5m/dGNkbi5uZXQvanBn/LzA5LzMwLzI0LzQ2/LzM2MF9GXzkzMDI0/NDY4N19XWHIyb1I2/dnhGVTdSYnRnQkRx/aHR1WTlOZTFXUmJz/RS5qcGc" alt="Phantom of the Opera Show"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>Phantom of the Opera</h4>
                    <p>Ongoing | West End</p>
                </div>
            </a>
            
            <a href="event.php?id=11" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/dS03GdNXiiRXoh8YPjQEI9FAk2gvbbLxfE2Olv_2Qxc/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5nZXR0eWltYWdl/cy5jb20vaWQvNTEx/MDYwNjMvcGhvdG8v/bG9uZG9uLWRhbmNl/cnMtZnJvbS10aGUt/Ym9sc2hvaS1iYWxs/ZXQtcGFydGljaXBh/dGUtaW4tYS10ZWNo/bmljYWwtcmVoZWFy/c2FsLW9mLXN3YW4t/bGFrZS1hdC10aGUu/anBnP3M9NjEyeDYx/MiZ3PTAmaz0yMCZj/PXN5WEg5azlhSUJs/eVhreFlaSmgyblRq/c2hvRnJIekN2OGp5/T2oxV3RRblE9" alt="Swan Lake Ballet"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>Swan Lake Ballet</h4>
                    <p>1 Dec | Vienna</p>
                </div>
            </a>
            
            <a href="event.php?id=12" class="event-card">
                <div class="event-card-media">
                    <img class="event-card-img" src="https://imgs.search.brave.com/TJUfXnpeDhvZbVdQOCnI5yzcDTwyJAv68K6zx_5C6eE/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9zZWF0/cGxhbi5jb20vY2Ru/L2ltYWdlcy9jL3No/b3cvbGlvbi1raW5n/LTIwMjQtaGVyby03/MTB3eDM1NWgtMTcy/MjM0OTY5Mi53ZWJw" alt="The Lion King Musical"/>
                    <span class="buy-button-overlay">Buy Tickets</span>
                </div>
                <div class="event-card-info">
                    <h4>The Lion King</h4>
                    <p>20 Jan | Hamburg</p>
                </div>
            </a>
        </div>
    </div>

</div>

    <div class="right-trending">
        <h2 class="section-title">Trending Events</h2>

        <a href="event.php?id=13" class="trend-card">
            <h4>Coldplay</h4>
            <p>12 Feb — Munich</p>
        </a>

        <a href="event.php?id=14" class="trend-card">
            <h4>UFC 312</h4>
            <p>25 Mar — Las Vegas</p>
        </a>

        <a href="event.php?id=15" class="trend-card">
            <h4>Real Madrid vs Barcelona</h4>
            <p>1 Apr — Madrid</p>
        </a>

        <a href="event.php?id=16" class="trend-card">
            <h4>Hamilton (Musical)</h4>
            <p>19 Jan — Broadway</p>
        </a>

        <a href="event.php?id=17" class="trend-card">
            <h4>Adele Residency</h4>
            <p>Every Sat — Caesars Palace</p>
        </a>
        <a href="event.php?id=18" class="trend-card">
            <h4>NFL London Games</h4>
            <p>1 Nov — Tottenham Hotspur</p>
        </a>
        <a href="event.php?id=19" class="trend-card">
            <h4>Disney on Ice</h4>
            <p>5 Dec — Chicago</p>
        </a>
        <a href="event.php?id=20" class="trend-card">
            <h4>F.C. Bayern vs Dortmund</h4>
            <p>10 May — Munich</p>
        </a>
    </div>

    </section>
    </div>
    <?php endif; ?>

    <footer>
        <p>© 2025-2026 TicketGeek</p>
        <a href="AboutUs.php">About Us</a> | <a href="FAQ.php">FAQ</a> | <a href="ContactUs.php">Contact Us</a>
    </footer>

</body>

</html>