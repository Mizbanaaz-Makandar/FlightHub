<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta title="FlightHub - Your Journey Begins Here">
    <title>FlightHub - Your Journey Begins Here</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* Hero Section with Animated Background */
        .hero {
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            animation: drift 60s linear infinite;
            opacity: 0.3;
        }

        @keyframes drift {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-50%, -50%); }
        }

        /* Floating Clouds */
        .cloud {
            position: absolute;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 100px;
            animation: float 20s infinite ease-in-out;
        }

        .cloud1 { width: 200px; height: 60px; top: 20%; left: -200px; animation-delay: 0s; }
        .cloud2 { width: 300px; height: 80px; top: 40%; left: -300px; animation-delay: 5s; }
        .cloud3 { width: 250px; height: 70px; top: 60%; left: -250px; animation-delay: 10s; }

        @keyframes float {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(calc(100vw + 300px)); }
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }

        nav.scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #00d4ff 0%, #0099ff 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 153, 255, 0.4);
            transition: all 0.3s;
        }

        .logo-icon::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 3px;
            background: white;
            border-radius: 2px;
            transform: rotate(-45deg);
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -6px;
        }

        .logo-icon::after {
            content: '';
            position: absolute;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 12px solid white;
            transform: rotate(45deg);
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -1px;
        }

        nav.scrolled .logo-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .logo:hover .logo-icon {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 153, 255, 0.5);
        }

        nav.scrolled .logo {
            color: #667eea;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }

        nav.scrolled .nav-links a {
            color: #333;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: white;
            transition: width 0.3s;
        }

        nav.scrolled .nav-links a::after {
            background: #667eea;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Hero Content */
        .hero-content {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 0 20px;
        }

        .hero-title {
            font-size: 64px;
            font-weight: bold;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
        }

        .hero-subtitle {
            font-size: 24px;
            margin-bottom: 30px;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s backwards;
        }

        .cta-button {
            padding: 18px 50px;
            background: white;
            color: #667eea;
            border: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            animation: fadeInUp 1s ease 0.4s backwards;
            text-decoration: none;
            display: inline-block;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 255, 255, 0.3);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }



        /* Features Section */
        .features {
            padding: 100px 50px;
            background: #f8f9fa;
        }

        .features h2 {
            text-align: center;
            font-size: 42px;
            margin-bottom: 60px;
            color: #333;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }

        .feature-card h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Destinations */
        .destinations {
            padding: 100px 50px;
        }

        .destinations h2 {
            text-align: center;
            font-size: 42px;
            margin-bottom: 60px;
            color: #333;
        }

        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .destination-card {
            position: relative;
            height: 350px;
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
        }

        .destination-card:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        .destination-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .destination-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
        }

        .destination-card h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .destination-card p {
            opacity: 0.9;
        }

        /* Footer */
        footer {
            background: #1a1a2e;
            color: white;
            padding: 50px;
            text-align: center;
        }

        footer p {
            margin-bottom: 20px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #667eea;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 36px;
            }

            .hero-subtitle {
                font-size: 18px;
            }

            .booking-form {
                padding: 25px;
            }

            nav {
                padding: 15px 20px;
            }

            .features, .destinations {
                padding: 60px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav id="navbar">
        <div class="logo">
            <div class="logo-icon"></div>
            <span>FlightHub</span>
        </div>
        <ul class="nav-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#bookings">My Bookings</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="signup.php">Create Account</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="cloud cloud1"></div>
        <div class="cloud cloud2"></div>
        <div class="cloud cloud3"></div>
        
        <div class="hero-content">
            <h1 class="hero-title">Fly Anywhere, Anytime</h1>
            <p class="hero-subtitle">Book your dream destination with ease</p>
            <a href="#destinations" class="cta-button">Explore Destinations</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2>Why Choose FlightHub?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>Best Prices</h3>
                <p>Compare prices across airlines and get the best deals on your flights.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure Booking</h3>
                <p>Your information is protected with state-of-the-art encryption technology.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Instant Confirmation</h3>
                <p>Get immediate booking confirmation and e-tickets sent to your email.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌍</div>
                <h3>Global Coverage</h3>
                <p>Access thousands of destinations worldwide with our extensive network.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📞</div>
                <h3>24/7 Support</h3>
                <p>Our customer service team is always ready to assist you anytime.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎁</div>
                <h3>Rewards Program</h3>
                <p>Earn points on every booking and redeem them for future travels.</p>
            </div>
        </div>
    </section>

    <!-- Popular Destinations -->
    <section class="destinations" id="destinations">
        <h2>Popular Destinations</h2>
        <div class="destinations-grid">
            <div class="destination-card">
                <img src="data:image/svg+xml,%3Csvg width='400' height='350' xmlns='http://www.w3.org/2000/svg'%3E%3Crect fill='%23FF6B6B' width='400' height='350'/%3E%3Ctext x='50%25' y='50%25' font-size='24' fill='white' text-anchor='middle' dy='.3em'%3EParis%3C/text%3E%3C/svg%3E" alt="Paris">
                <div class="destination-overlay">
                    <h3>Paris, France</h3>
                    <p>From $499</p>
                </div>
            </div>
            <div class="destination-card">
                <img src="data:image/svg+xml,%3Csvg width='400' height='350' xmlns='http://www.w3.org/2000/svg'%3E%3Crect fill='%234ECDC4' width='400' height='350'/%3E%3Ctext x='50%25' y='50%25' font-size='24' fill='white' text-anchor='middle' dy='.3em'%3ETokyo%3C/text%3E%3C/svg%3E" alt="Tokyo">
                <div class="destination-overlay">
                    <h3>Tokyo, Japan</h3>
                    <p>From $799</p>
                </div>
            </div>
            <div class="destination-card">
                <img src="data:image/svg+xml,%3Csvg width='400' height='350' xmlns='http://www.w3.org/2000/svg'%3E%3Crect fill='%2395E1D3' width='400' height='350'/%3E%3Ctext x='50%25' y='50%25' font-size='24' fill='white' text-anchor='middle' dy='.3em'%3ENew York%3C/text%3E%3C/svg%3E" alt="New York">
                <div class="destination-overlay">
                    <h3>New York, USA</h3>
                    <p>From $399</p>
                </div>
            </div>
            <div class="destination-card">
                <img src="data:image/svg+xml,%3Csvg width='400' height='350' xmlns='http://www.w3.org/2000/svg'%3E%3Crect fill='%23F38181' width='400' height='350'/%3E%3Ctext x='50%25' y='50%25' font-size='24' fill='white' text-anchor='middle' dy='.3em'%3EDubai%3C/text%3E%3C/svg%3E" alt="Dubai">
                <div class="destination-overlay">
                    <h3>Dubai, UAE</h3>
                    <p>From $699</p>
                </div>
            </div>
            <div class="destination-card">
                <img src="data:image/svg+xml,%3Csvg width='400' height='350' xmlns='http://www.w3.org/2000/svg'%3E%3Crect fill='%23AA96DA' width='400' height='350'/%3E%3Ctext x='50%25' y='50%25' font-size='24' fill='white' text-anchor='middle' dy='.3em'%3ELondon%3C/text%3E%3C/svg%3E" alt="London">
                <div class="destination-overlay">
                    <h3>London, UK</h3>
                    <p>From $549</p>
                </div>
            </div>
            <div class="destination-card">
                <img src="data:image/svg+xml,%3Csvg width='400' height='350' xmlns='http://www.w3.org/2000/svg'%3E%3Crect fill='%23FCBAD3' width='400' height='350'/%3E%3Ctext x='50%25' y='50%25' font-size='24' fill='white' text-anchor='middle' dy='.3em'%3EBali%3C/text%3E%3C/svg%3E" alt="Bali">
                <div class="destination-overlay">
                    <h3>Bali, Indonesia</h3>
                    <p>From $599</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <p>&copy; 2025 FlightHub Airlines. All rights reserved.</p>
        <p>Contact: info@flighthub.com | +1 (555) 123-4567</p>
        <div class="social-links">
            <a href="#" title="Facebook">f</a>
            <a href="#" title="Twitter">𝕏</a>
            <a href="#" title="Instagram">📷</a>
            <a href="#" title="LinkedIn">in</a>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>