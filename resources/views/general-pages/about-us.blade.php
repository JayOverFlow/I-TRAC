<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - I-TRAC</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
    <div class="about-us-container">
        <!-- Top Header (Back button and Logo left-aligned and stacked) -->
        <div class="top-header-section">
            <a href="javascript:void(0);" onclick="goBack()" class="btn-back">
                <img src="{{ asset('img/Back.svg') }}" alt="Back" class="back-arrow-img">
                <span class="back-text">ABOUT</span>
            </a>
            <div class="logo-container">
                <img src="{{ asset('img/itrac-header-logo-white.svg') }}" alt="i-TRAC Logo" class="logo-img">
            </div>
        </div>

        <div class="about-us-content-body">
            <p class="general-desc">
                I-TRAC is an integrated procurement and lifecycle tracking system designed to streamline, digitize, and monitor the end-to-end purchasing workflow of the institution. The system automates critical operational phases—from the initial system configuration of the Annual Procurement Plan (APP) by the College Dean, to Purchase Order (PO) generation by the Procurement Office, and ultimately to delivery documentation and material allocation by the Supply Office. By leveraging smart verification features like unique tracking codes and mobile QR scanning, i-TRAC transitions manual, offline approvals into a seamless, highly accountable, and paperless digital ecosystem.

            </p>

            <!-- Mission & Vision staggered block -->
            <div class="staggered-section">
                <div class="staggered-row">
                    <div class="staggered-title">MISSION</div>
                    <div class="staggered-card">
                        <p>To provide an efficient, transparent, and reliable procurement management platform that automates procurement workflows, strengthens accountability, improves document tracking, and supports effective resource utilization through digital transformation and real-time monitoring.</p>
                    </div>
                </div>
                
                <div class="staggered-row reverse" style="margin-bottom: 40px;">
                    <div class="staggered-title">VISION</div>
                    <div class="staggered-card">
                        <p>To become a leading digital procurement and inventory management standard for institutional resource management, fostering a culture of fiscal responsibility, zero-bottleneck workflows, and flawless inventory traceability across all colleges and administrative offices.</p>
                    </div>
                </div>
            </div>

            <!-- Project Developers Section Title -->
            <div class="developers-header">
                <h2 class="section-title">
                    LEARN MORE ABOUT THE<br>
                    PROJECT DEVELOPERS
                </h2>
                <p class="section-subtitle">
                    Discover the visionaries behind this project and their commitment to excellence.
                </p>
            </div>

            <!-- Interactive Developers Grid -->
            <div class="developers-section">
                <div class="developers-grid">

                    <!-- Developer 1 (John Brix Coronejo) -->
                    <div class="developer-tile active" data-index="4">
                        <div class="avatar-container">
                            <img src="{{ asset('img/developers/brix.svg') }}" alt="John Brix Coronejo" class="avatar-img">
                        </div>
                        <div class="developer-info-box">
                            <div class="developer-tile-name">John Brix Coronejo</div>
                            <div class="developer-tile-role">Frontend Developer & Quality Assurance Tester</div>
                        </div>
                    </div>

                    <!-- Developer 2 (John Rex Duran) -->
                    <div class="developer-tile" data-index="3">
                        <div class="avatar-container">
                            <img src="{{ asset('img/developers/rex.svg') }}" alt="John Rex Duran" class="avatar-img">
                        </div>
                        <div class="developer-info-box">
                            <div class="developer-tile-name">John Rex Duran</div>
                            <div class="developer-tile-role">Full Stack Developer</div>
                        </div>
                    </div>

                    <!-- Developer 3 (Merielle Grace Esplana) -->
                    <div class="developer-tile" data-index="5">
                        <div class="avatar-container">
                            <img src="{{ asset('img/developers/grace.svg') }}" alt="Merielle Grace Esplana" class="avatar-img">
                        </div>  
                        <div class="developer-info-box">
                            <div class="developer-tile-name">Merielle Grace Esplana</div>
                            <div class="developer-tile-role">Project Manager</div>
                        </div>
                    </div>

                     <!-- Developer 4 (Emmanuel Ferrer) -->
                    <div class="developer-tile" data-index="2">
                        <div class="avatar-container">
                            <img src="{{ asset('img/developers/emman.svg') }}" alt="Emmanuel Ferrer" class="avatar-img">
                        </div>
                        <div class="developer-info-box">
                            <div class="developer-tile-name">Emmanuel Ferrer</div>
                            <div class="developer-tile-role">Database Administrator & Mobile App Developer</div>
                        </div>
                    </div>

                    <!-- Developer 5 (Kimberlie Porteria) -->
                    <div class="developer-tile" data-index="0">
                        <div class="avatar-container">
                            <img src="{{ asset('img/developers/kim.svg') }}" alt="Kimberlie Porteria" class="avatar-img">
                        </div>
                        <div class="developer-info-box">
                            <div class="developer-tile-name">Kimberlie Porteria</div>
                            <div class="developer-tile-role">UI/UX Designer</div>
                        </div>
                    </div>

                    <!-- Developer 6 (Aliah Wales) -->
                    <div class="developer-tile" data-index="1">
                        <div class="avatar-container">
                            <img src="{{ asset('img/developers/aliah.svg') }}" alt="Aliah T. Wales" class="avatar-img">
                        </div>
                        <div class="developer-info-box">
                            <div class="developer-tile-name">Aliah Wales</div>
                            <div class="developer-tile-role">UI/UX Designer</div>
                        </div>
                    </div>

                </div>

                <!-- Dynamic Info Card -->
                <div class="info-card" id="detail-card-pane">
                    <div class="info-card-left">
                        <div class="info-card-photo-container">
                            <img src="{{ asset('img/developers/brix.svg') }}" alt="John Brix G. Coronejo" class="info-card-photo" id="card-photo-pane">
                        </div>
                    </div>
                    <div class="info-card-right">
                        <h3 class="info-card-greeting" id="card-greeting-pane">Hi! I am John Brix G. Coronejo</h3>
                        <div class="info-card-role-email">
                            <span class="info-card-role" id="card-role-pane">Frontend Developer &amp; Quality Assurance Tester</span>
                            <a href="mailto:coronejojohnbrix16@gmail.com" class="info-card-email" id="card-email-pane">
                                <i class="far fa-envelope"></i> <span id="card-email-text-pane">coronejojohnbrix16@gmail.com</span>
                            </a>
                        </div>
                        <p class="info-card-bio" id="card-bio-pane">
                            Aspiring tech professional with a dual focus on frontend development and QA testing. Enthusiastic about creating highly interactive user interfaces and maintaining top-tier software quality through thorough manual and automated testing workflows.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer-section">
                <p class="footer-text">Copyright &copy; 2026 i-TRAC, All rights reserved.</p>
            </div>
        </div> <!-- End about-us-content-body -->
    </div>

     <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #900b09;
            background-image: url("{{ asset('img/about-us-bg.svg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            color: #ffffff;
            padding: 40px;
        }

        .about-us-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 35px;
        }
        
        .top-header-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            color: #ffffff !important;
            text-decoration: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
            cursor: pointer;
        }

        .back-arrow-img {
            filter: brightness(0) invert(1);
            width: 29px;
            height: 28px;
            margin-right: 45px;
        }

        .back-text {
            font-size: 2.2rem;
            font-weight: 300;
            letter-spacing: 2px;
            line-height: 1;
        }

        .btn-back:hover {
            opacity: 0.8;
            transform: translateX(-4px);
        }

        .logo-container {
            width: 420px;
            max-width: calc(100% - 148px);
            margin-top: 5px;
            margin-left: 74px;
            margin-right: 74px;
        }

        .logo-img {
            width: 100%;
            height: auto;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.15));
        }

        .about-us-content-body {
            width: calc(100% - 148px);
            margin-left: 74px;
            margin-right: 74px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 35px;
        }

        .general-desc {
            font-size: 1.05rem;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.95);
            text-align: justify;
            width: 100%;
            max-width: 100%;
            font-weight: 400;
            letter-spacing: 0.3px;
            margin: 0;
        }

        /* Mission & Vision Section */
        .staggered-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .staggered-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
            width: 100%;
        }

        .staggered-row.reverse {
            flex-direction: row-reverse;
        }

        .staggered-title {
            flex: 1;
            font-size: 2.8rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .staggered-row:not(.reverse) .staggered-title {
            text-align: left;
        }

        .staggered-row.reverse .staggered-title {
            text-align: right;
        }

        .staggered-card {
            flex: 1.6;
            background: #ffffff;
            border-radius: 14px;
            padding: 24px 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            color: #2c3e50;
            transition: transform 0.3s ease;
        }

        .staggered-card p {
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
            color: #4a4a4a;
        }

        /* Section Heading */
        .developers-header {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        .section-title {
            font-size: 2.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
            color: #ffffff;
            margin: 0;
            text-align: left;
        }

        .section-subtitle {
            font-size: 1.15rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.95);
            margin: 0;
            text-align: left;
        }

        /* Developers Section */
        .developers-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .developers-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
            width: 100%;
        }

        .developer-tile {
            background: #ffffff;
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            height: 100%;
            justify-content: space-between;
        }

        .developer-tile:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 22px rgba(0,0,0,0.15);
        }

        .developer-tile.active {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.18);
        }

        .avatar-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 8px;
            background-color: #f5f5f5;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .developer-tile:hover .avatar-img {
            transform: scale(1.05);
        }

        .developer-info-box {
            width: 100%;
            padding: 6px 4px;
            border-radius: 6px;
            background-color: transparent;
            transition: background-color 0.2s ease;
        }

        .developer-tile.active .developer-info-box {
            background-color: #800000; /* Solid dark red */
        }

        .developer-tile-name {
            font-size: 0.8rem;
            font-weight: 700;
            color: #900b09;
            margin-bottom: 2px;
            transition: color 0.2s ease;
            word-wrap: break-word;
        }

        .developer-tile-role {
            font-size: 0.7rem;
            color: #777777;
            transition: color 0.2s ease;
            word-wrap: break-word;
            line-height: 1.2;
        }

        .developer-tile.active .developer-tile-name {
            color: #ffffff;
        }

        .developer-tile.active .developer-tile-role {
            color: #dddddd;
        }

        /* Detail Info Card */
        .info-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            display: flex;
            gap: 30px;
            width: 100%;
            box-shadow: 0 12px 28px rgba(0,0,0,0.15);
            color: #333333;
            align-items: stretch;
            min-height: 240px;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .info-card-left {
            flex: 0 0 180px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .info-card-photo-container {
            width: 100%;
            height: 100%;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .info-card-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-card-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 10px;
            text-align: left;
        }

        .info-card-greeting {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1a1a1a;
            margin: 0;
        }

        .info-card-role-email {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-card-role {
            font-size: 1.1rem;
            font-weight: 700;
            color: #900b09;
        }

        .info-card-email {
            font-size: 0.9rem;
            color: #666666;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s ease;
        }

        .info-card-email:hover {
            color: #900b09;
        }

        .info-card-bio {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #555555;
            margin-top: 6px;
        }

        /* Footer */
        .footer-section {
            width: 100%;
            text-align: center;
            padding-top: 15px;
        }

        .footer-text {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.75);
            font-weight: 400;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .developers-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 24px 20px;
            }
            .about-us-content-body {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
            }
            .logo-container {
                margin-left: 0;
                margin-right: 0;
                max-width: 100%;
            }
            .staggered-row, 
            .staggered-row.reverse {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .staggered-row:not(.reverse) .staggered-title,
            .staggered-row.reverse .staggered-title {
                text-align: center;
                font-size: 2.2rem;
            }
            .info-card {
                flex-direction: column;
                padding: 24px;
                align-items: center;
                text-align: center;
                min-height: auto;
            }
            .info-card-left {
                flex: 0 0 160px;
                width: 160px;
                height: 200px;
            }
            .info-card-right {
                align-items: center;
                text-align: center;
            }
            .info-card-greeting {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .developers-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            .logo-container {
                width: 160px;
            }
            .section-title {
                font-size: 1.6rem;
            }
        }
    </style>

    <script>
        // Developer Data
        const paneDevelopers = [
            {
                name: "Kimberlie Crissel F. Porteria",
                role: "UI/UX Designer",
                email: "porteriakimberlie@gmail.com",
                photo: "{{ asset('img/developers/kim.svg') }}",
                bio: "Aspiring UI/UX Designer committed to learning, innovating, and creating meaningful human-computer interactions. I love collaborating on cross-functional teams to bring creative ideas to life through empathy-driven design."
            },
            {
                name: "Aliah T. Wales",
                role: "UI/UX Designer",
                email: "whalesaliaht@gmail.com",
                photo: "{{ asset('img/developers/aliah.svg') }}",
                bio: "Aspiring UI/UX Designer driven by curiosity, design empathy, and a love for modern interface aesthetics. Eager to collaborate on cross-functional teams to translate user insights into engaging, accessible, and high-impact web and mobile designs."
            },
            {
                name: "Emmanuel Ferrer",
                role: "Database Administrator & Mobile App Developer",
                email: "emmanferrer753@gmail.com",
                photo: "{{ asset('img/developers/emman.svg') }}",
                bio: "Aspiring developer with a dual focus on mobile application development and database management systems. Eager to leverage my technical skills in structuring data and building cross-platform mobile apps to deliver powerful, user-centric digital solutions."
            },
            {
                name: "John Rex B. Duran",
                role: "Full Stack Developer",
                email: "jayoverflow29@gmail.com",
                photo: "{{ asset('img/developers/rex.svg') }}",
                bio: "Aspiring Full Stack Developer dedicated to writing clean, maintainable code and learning the latest web technologies. Enthusiastic about collaborating on impactful projects and building reliable web applications from the ground up."
            },
            {
                name: "John Brix G. Coronejo",
                role: "Frontend Developer & Quality Assurance Tester",
                email: "coronejojohnbrix16@gmail.com",
                photo: "{{ asset('img/developers/brix.svg') }}",
                bio: "Aspiring tech professional with a dual focus on frontend development and QA testing. Enthusiastic about creating highly interactive user interfaces and maintaining top-tier software quality through thorough manual and automated testing workflows."
            },
            {
                name: "Merielle Grace Esplana",
                role: "Project Manager",
                email: "graceyesplana01@gmail.com",
                photo: "{{ asset('img/developers/grace.svg') }}",
                bio: "Aspiring Project Manager with a strong foundation in team leadership, risk management, and Agile methodologies. Enthusiastic about fostering collaboration, optimizing team workflows, and steering tech initiatives from initial concept to successful launch."
            }
        ];

        function goBack() {
            if (document.referrer && document.referrer.includes(window.location.hostname)) {
                history.back();
            } else {
                window.location.href = "{{ route('account.settings') }}";
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            const tiles = document.querySelectorAll(".developer-tile");
            const cardPhoto = document.getElementById("card-photo-pane");
            const cardGreeting = document.getElementById("card-greeting-pane");
            const cardRole = document.getElementById("card-role-pane");
            const cardEmail = document.getElementById("card-email-pane");
            const cardEmailText = document.getElementById("card-email-text-pane");
            const cardBio = document.getElementById("card-bio-pane");
            const detailCard = document.getElementById("detail-card-pane");

            tiles.forEach(tile => {
                tile.addEventListener("click", (e) => {
                    e.preventDefault();
                    
                    // Remove active class from all
                    tiles.forEach(t => t.classList.remove("active"));
                    
                    // Add active class to clicked
                    tile.classList.add("active");

                    // Get developer index
                    const index = parseInt(tile.getAttribute("data-index"));
                    const dev = paneDevelopers[index];

                    // Transition out effect
                    detailCard.style.opacity = "0";
                    detailCard.style.transform = "scale(0.98)";

                    setTimeout(() => {
                        // Update card content
                        cardPhoto.src = dev.photo;
                        cardPhoto.alt = dev.name;
                        cardGreeting.textContent = `Hi! I am ${dev.name}`;
                        cardRole.textContent = dev.role;
                        cardEmail.href = `mailto:${dev.email}`;
                        cardEmailText.textContent = dev.email;
                        cardBio.textContent = dev.bio;

                        // Transition back in
                        detailCard.style.opacity = "1";
                        detailCard.style.transform = "scale(1)";
                    }, 200);
                });
            });
        });
    </script>
</body>
</html>
