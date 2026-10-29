@if(\App\Services\FeatureManager::isHalloweenThemeEnabled())
<div class="halloween-decorations-wrapper pointer-events-none select-none overflow-hidden" x-show="$store.theme.halloween" x-cloak>
    {{-- Full-Viewport Atmospheric Halloween Background Scene --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden halloween-backdrop">
        {{-- Dark Mode Haunted Castle & Midnight Graveyard Scene --}}
        <div class="hidden dark:block absolute inset-0 w-full h-full opacity-35 transition-opacity duration-500">
            <svg class="w-full h-full object-cover" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <!-- Moon Glow Filter -->
                    <filter id="hwMoonGlow" x="-50%" y="-50%" width="200%" height="200%">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="16" result="blur1"/>
                        <feGaussianBlur in="SourceGraphic" stdDeviation="32" result="blur2"/>
                        <feMerge>
                            <feMergeNode in="blur2"/>
                            <feMergeNode in="blur1"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                    <!-- Fog Gradient -->
                    <linearGradient id="hwFogGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#8B5CF6" stop-opacity="0"/>
                        <stop offset="60%" stop-color="#16131F" stop-opacity="0.35"/>
                        <stop offset="100%" stop-color="#0B0A0F" stop-opacity="0.8"/>
                    </linearGradient>
                    <linearGradient id="hwMoonHalo" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#FDE047" stop-opacity="0.25"/>
                        <stop offset="40%" stop-color="#F97316" stop-opacity="0.12"/>
                        <stop offset="100%" stop-color="#0B0A0F" stop-opacity="0"/>
                    </linearGradient>
                </defs>

                {{-- Twinkling Midnight Stars --}}
                <g class="halloween-stars opacity-60">
                    <circle cx="120" cy="90" r="1.2" fill="#FDE047" class="star-twinkle-1"/>
                    <circle cx="280" cy="140" r="1" fill="#FFFFFF" class="star-twinkle-2"/>
                    <circle cx="430" cy="70" r="1.5" fill="#F97316" class="star-twinkle-3"/>
                    <circle cx="620" cy="120" r="1" fill="#FFFFFF" class="star-twinkle-1"/>
                    <circle cx="750" cy="60" r="1.4" fill="#A855F7" class="star-twinkle-2"/>
                    <circle cx="910" cy="110" r="1.2" fill="#FDE047" class="star-twinkle-3"/>
                    <circle cx="1060" cy="80" r="1.5" fill="#FFFFFF" class="star-twinkle-1"/>
                    <circle cx="1340" cy="130" r="1.3" fill="#F97316" class="star-twinkle-2"/>
                    <circle cx="180" cy="220" r="1" fill="#A855F7" class="star-twinkle-3"/>
                    <circle cx="510" cy="250" r="1.2" fill="#FFFFFF" class="star-twinkle-1"/>
                    <circle cx="1250" cy="240" r="1" fill="#FDE047" class="star-twinkle-2"/>
                </g>

                {{-- Glowing Full Harvest Moon & Halo --}}
                <g class="halloween-moon">
                    <!-- Outer atmospheric halo -->
                    <circle cx="1180" cy="170" r="160" fill="url(#hwMoonHalo)" />
                    <!-- Moon Sphere with glow -->
                    <circle cx="1180" cy="170" r="75" fill="#FEF08A" filter="url(#hwMoonGlow)" opacity="0.9" />
                    <circle cx="1180" cy="170" r="75" fill="#FDE047" />
                    <!-- Craters -->
                    <ellipse cx="1160" cy="155" rx="14" ry="11" fill="#EAB308" opacity="0.35" />
                    <circle cx="1200" cy="190" r="10" fill="#EAB308" opacity="0.3" />
                    <circle cx="1145" cy="185" r="7" fill="#EAB308" opacity="0.25" />
                    <ellipse cx="1195" cy="145" rx="9" ry="6" fill="#EAB308" opacity="0.25" />
                    <!-- Drifting Wispy Clouds over Moon -->
                    <path d="M1090 155 Q 1140 140 1200 158 Q 1245 162 1270 150 Q 1230 168 1170 166 Q 1120 172 1090 155 Z" fill="#16131F" opacity="0.65" class="cloud-drift-1" />
                    <path d="M1120 185 Q 1165 175 1220 190 Q 1255 188 1285 180 Q 1245 198 1190 196 Q 1150 200 1120 185 Z" fill="#16131F" opacity="0.55" class="cloud-drift-2" />
                </g>

                {{-- Distant Haunted Mountain / Hill Silhouettes --}}
                <path d="M0 650 Q 250 580 520 630 Q 820 690 1100 600 Q 1280 540 1440 590 L 1440 900 L 0 900 Z" fill="#110C1B" opacity="0.6"/>
                <path d="M0 720 Q 300 660 650 710 Q 1000 760 1250 690 Q 1380 660 1440 680 L 1440 900 L 0 900 Z" fill="#0D0916" opacity="0.85"/>

                {{-- Majestic Gothic Haunted Manor on the Ridge (Center-Right) --}}
                <g class="haunted-castle" transform="translate(860, 420)">
                    <!-- Manor Base & Wings -->
                    <rect x="120" y="110" width="160" height="130" fill="#0A0710" />
                    <rect x="60" y="150" width="60" height="90" fill="#0A0710" />
                    <rect x="280" y="150" width="60" height="90" fill="#0A0710" />
                    <!-- Main Central Spire & Roof -->
                    <polygon points="120,110 200,10 280,110" fill="#0A0710" />
                    <polygon points="195,10 200,-35 205,10" fill="#0A0710" />
                    <!-- Cross / Finial atop central spire -->
                    <line x1="200" y1="-45" x2="200" y2="-20" stroke="#0A0710" stroke-width="2.5"/>
                    <line x1="194" y1="-38" x2="206" y2="-38" stroke="#0A0710" stroke-width="2"/>
                    <!-- Left Turret -->
                    <rect x="50" y="110" width="30" height="120" fill="#0A0710" />
                    <polygon points="45,110 65,40 85,110" fill="#0A0710" />
                    <line x1="65" y1="30" x2="65" y2="45" stroke="#0A0710" stroke-width="2"/>
                    <!-- Right Turret -->
                    <rect x="320" y="110" width="30" height="120" fill="#0A0710" />
                    <polygon points="315,110 335,40 355,110" fill="#0A0710" />
                    <line x1="335" y1="30" x2="335" y2="45" stroke="#0A0710" stroke-width="2"/>
                    <!-- Battlements -->
                    <polygon points="120,110 135,95 150,110 165,95 180,110" fill="#0A0710" />
                    <polygon points="220,110 235,95 250,110 265,95 280,110" fill="#0A0710" />

                    <!-- Flickering Candlelit Castle Windows -->
                    <g class="castle-windows">
                        <!-- Main central grand gothic window -->
                        <path d="M190 75 Q 200 60 210 75 L 210 100 L 190 100 Z" fill="#F59E0B" class="window-flicker-1"/>
                        <line x1="200" y1="65" x2="200" y2="100" stroke="#0A0710" stroke-width="1.2"/>
                        <line x1="190" y1="85" x2="210" y2="85" stroke="#0A0710" stroke-width="1.2"/>
                        <!-- Lower windows -->
                        <rect x="145" y="130" width="14" height="22" rx="3" fill="#F97316" class="window-flicker-2"/>
                        <rect x="175" y="130" width="14" height="22" rx="3" fill="#F59E0B" class="window-flicker-1"/>
                        <rect x="210" y="130" width="14" height="22" rx="3" fill="#F59E0B" class="window-flicker-3"/>
                        <rect x="240" y="130" width="14" height="22" rx="3" fill="#F97316" class="window-flicker-2"/>
                        <!-- Turret slit windows -->
                        <rect x="62" y="135" width="6" height="14" rx="2" fill="#F59E0B" class="window-flicker-1"/>
                        <rect x="332" y="135" width="6" height="14" rx="2" fill="#F59E0B" class="window-flicker-3"/>
                    </g>
                </g>

                {{-- Spooky Gnarled Trees on Sides --}}
                <!-- Left Dead Tree -->
                <path d="M -20 850 C 40 760 60 680 50 580 C 40 500 20 450 -10 400 M 50 580 C 80 530 130 490 170 470 M 110 510 C 140 450 160 410 180 370 M 120 480 C 80 430 70 380 60 340 M 50 630 C 10 590 -20 560 -40 550"
                      stroke="#0A0710" stroke-width="18" stroke-linecap="round" fill="none" opacity="0.95"/>
                <path d="M -20 850 C 40 760 60 680 50 580 C 40 500 20 450 -10 400 M 50 580 C 80 530 130 490 170 470 M 110 510 C 140 450 160 410 180 370 M 120 480 C 80 430 70 380 60 340"
                      stroke="#16131F" stroke-width="10" stroke-linecap="round" fill="none" opacity="0.8"/>

                <!-- Right Dead Tree Frame -->
                <path d="M 1460 820 C 1400 730 1380 640 1390 540 C 1400 470 1430 420 1470 370 M 1390 540 C 1350 490 1290 460 1240 440 M 1320 475 C 1300 420 1280 370 1260 330 M 1340 510 C 1370 450 1390 400 1400 350"
                      stroke="#0A0710" stroke-width="16" stroke-linecap="round" fill="none" opacity="0.95"/>

                {{-- Graveyard Silhouettes & Jack-o'-Lanterns along Bottom --}}
                <g class="graveyard" transform="translate(140, 750)">
                    <!-- Tombstones & Crosses -->
                    <path d="M 40 60 L 40 15 Q 52 5 65 15 L 65 60 Z" fill="#0A0710"/>
                    <path d="M 160 60 L 160 25 L 150 25 L 150 15 L 160 15 L 160 0 L 170 0 L 170 15 L 180 15 L 180 25 L 170 25 L 170 60 Z" fill="#0A0710"/>
                    <path d="M 280 60 L 285 20 Q 295 12 305 20 L 310 60 Z" fill="#0A0710" transform="rotate(-6 295 40)"/>
                    <!-- Crooked Wrought Iron Fence -->
                    <path d="M 360 45 L 360 15 L 365 10 L 370 15 L 370 45 M 390 45 L 390 12 L 395 7 L 400 12 L 400 45 M 420 45 L 420 16 L 425 11 L 430 16 L 430 45 M 350 30 L 440 28 M 350 40 L 440 38" stroke="#0A0710" stroke-width="2"/>
                    <!-- Glowing Jack-o'-Lantern on Hill -->
                    <g transform="translate(480, 20) scale(0.65)" class="jack-lantern-glow">
                        <ellipse cx="25" cy="22" rx="20" ry="16" fill="#F97316"/>
                        <path d="M 25 6 Q 28 0 32 -2" stroke="#15803D" stroke-width="3" fill="none"/>
                        <polygon points="16,18 20,20 18,14" fill="#0B0A0F"/>
                        <polygon points="34,18 30,20 32,14" fill="#0B0A0F"/>
                        <polygon points="25,21 23,25 27,25" fill="#0B0A0F"/>
                        <path d="M 14 28 Q 25 35 36 28 Q 25 32 14 28 Z" fill="#0B0A0F"/>
                    </g>
                </g>

                {{-- Drifting Mist / Fog Layer across Viewport --}}
                <rect x="0" y="600" width="1440" height="300" fill="url(#hwFogGrad)" />
            </svg>
        </div>

        {{-- Light Mode Autumn Pumpkin Patch & Rolling Hills Scene --}}
        <div class="block dark:hidden absolute inset-0 w-full h-full opacity-20 transition-opacity duration-500">
            <svg class="w-full h-full object-cover" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Warm Harvest Sun Halo -->
                <circle cx="1180" cy="180" r="140" fill="#FDBA74" opacity="0.25"/>
                <circle cx="1180" cy="180" r="70" fill="#FB923C" opacity="0.35"/>
                <!-- Rolling Autumn Hills -->
                <path d="M0 680 Q 320 620 680 670 Q 1040 720 1440 640 L 1440 900 L 0 900 Z" fill="#FDBA74" opacity="0.3"/>
                <path d="M0 750 Q 420 700 840 760 Q 1200 710 1440 740 L 1440 900 L 0 900 Z" fill="#FB923C" opacity="0.25"/>
                <!-- Bare Autumn Branches (Left) -->
                <path d="M -20 850 C 40 760 50 680 40 580 C 30 500 10 450 -15 400 M 40 580 C 70 530 110 490 150 470 M 90 510 C 120 450 140 410 160 370"
                      stroke="#78350F" stroke-width="12" stroke-linecap="round" fill="none" opacity="0.45"/>
                <!-- Bare Autumn Branches (Right) -->
                <path d="M 1460 820 C 1400 730 1380 640 1390 540 C 1400 470 1430 420 1470 370 M 1390 540 C 1350 490 1290 460 1240 440"
                      stroke="#78350F" stroke-width="12" stroke-linecap="round" fill="none" opacity="0.45"/>
                <!-- Distant Manor Silhouette -->
                <g transform="translate(900, 480) scale(0.7)" opacity="0.35">
                    <rect x="120" y="110" width="160" height="130" fill="#78350F" />
                    <polygon points="120,110 200,10 280,110" fill="#78350F" />
                    <rect x="50" y="110" width="30" height="120" fill="#78350F" />
                    <polygon points="45,110 65,40 85,110" fill="#78350F" />
                    <rect x="320" y="110" width="30" height="120" fill="#78350F" />
                    <polygon points="315,110 335,40 355,110" fill="#78350F" />
                </g>
            </svg>
        </div>
    </div>

    {{-- Top-left Corner Spooky Web Accent --}}
    <div class="fixed top-0 left-0 z-40 pointer-events-none w-28 h-28 opacity-40 dark:opacity-45">
        <svg viewBox="0 0 100 100" class="w-full h-full fill-none stroke-[#F97316]/50 dark:stroke-purple-400/60" stroke-width="0.8">
            <!-- Radial Web Lines -->
            <line x1="0" y1="0" x2="100" y2="0" stroke-dasharray="2,2"/>
            <line x1="0" y1="0" x2="90" y2="35"/>
            <line x1="0" y1="0" x2="70" y2="70"/>
            <line x1="0" y1="0" x2="35" y2="90"/>
            <line x1="0" y1="0" x2="0" y2="100" stroke-dasharray="2,2"/>
            <!-- Web Arcs -->
            <path d="M 25,0 Q 20,10 0,25" />
            <path d="M 50,0 Q 40,22 0,50" />
            <path d="M 75,0 Q 60,35 0,75" />
            <path d="M 100,0 Q 80,50 0,100" />
            <path d="M 35,12 Q 28,28 12,35" />
            <path d="M 60,20 Q 48,48 20,60" />
            <path d="M 85,28 Q 68,68 28,85" />
        </svg>
    </div>

    {{-- Dangling Swaying Spider from Navbar --}}
    <div class="fixed top-0 left-36 z-50 pointer-events-auto cursor-pointer group spider-dangle">
        <div class="spider-thread w-[1px] bg-slate-400/70 dark:bg-amber-100/40 h-9 mx-auto origin-top transition-all duration-300 group-hover:h-14"></div>
        <div class="spider-body relative -mt-0.5">
            <svg width="22" height="22" viewBox="0 0 24 24" class="filter drop-shadow-[0_2px_10px_rgba(249,115,22,0.45)]">
                <!-- Spider Body -->
                <ellipse cx="12" cy="14" rx="4.5" ry="5.5" class="fill-[#1A1225] dark:fill-[#0B0A0F] stroke-[#F97316] stroke-[1]"/>
                <circle cx="12" cy="7.5" r="3" class="fill-[#1A1225] dark:fill-[#0B0A0F] stroke-[#F97316] stroke-[1]"/>
                <!-- Glowing Pulsing Red-Orange Eyes -->
                <circle cx="10.8" cy="7.2" r="1" fill="#FF3B30" class="spider-eye-pulse"/>
                <circle cx="13.2" cy="7.2" r="1" fill="#FF3B30" class="spider-eye-pulse"/>
                <!-- Left Legs -->
                <path d="M8.5 12 C 5 9, 3 10, 1 12" stroke="#F97316" stroke-width="1" fill="none" stroke-linecap="round"/>
                <path d="M8.5 14 C 4 13, 2 15, 1 18" stroke="#F97316" stroke-width="1" fill="none" stroke-linecap="round"/>
                <path d="M8.5 16 C 5 17, 3 20, 2 23" stroke="#F97316" stroke-width="1" fill="none" stroke-linecap="round"/>
                <!-- Right Legs -->
                <path d="M15.5 12 C 19 9, 21 10, 23 12" stroke="#F97316" stroke-width="1" fill="none" stroke-linecap="round"/>
                <path d="M15.5 14 C 20 13, 22 15, 23 18" stroke="#F97316" stroke-width="1" fill="none" stroke-linecap="round"/>
                <path d="M15.5 16 C 19 17, 21 20, 22 23" stroke="#F97316" stroke-width="1" fill="none" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    {{-- Animated Flying Bats Across Viewport --}}
    <div class="fixed top-3 left-1/4 z-30 pointer-events-none bat-flying bat-1 opacity-70 dark:opacity-80">
        <svg width="26" height="16" viewBox="0 0 32 18" class="fill-neutral-900 dark:fill-[#120A1A] stroke-[#F97316] stroke-[0.8] filter drop-shadow-[0_2px_6px_rgba(249,115,22,0.3)]">
            <path d="M16 8 C14 3, 9 0, 3 1 C2 5, 4 9, 8 12 C5 12, 2 14, 0 17 C5 17, 10 14, 13 13 C14 15, 15 15, 16 15 C17 15, 18 15, 19 13 C22 14, 27 17, 32 17 C30 14, 27 12, 24 12 C28 9, 30 5, 29 1 C23 0, 18 3, 16 8 Z"/>
        </svg>
    </div>

    <div class="fixed top-8 right-1/4 z-30 pointer-events-none bat-flying bat-2 hidden md:block opacity-60 dark:opacity-75">
        <svg width="22" height="13" viewBox="0 0 32 18" class="fill-neutral-900 dark:fill-[#1E132B] stroke-[#A855F7] stroke-[0.8] filter drop-shadow-[0_2px_6px_rgba(168,85,247,0.3)]">
            <path d="M16 8 C14 3, 9 0, 3 1 C2 5, 4 9, 8 12 C5 12, 2 14, 0 17 C5 17, 10 14, 13 13 C14 15, 15 15, 16 15 C17 15, 18 15, 19 13 C22 14, 27 17, 32 17 C30 14, 27 12, 24 12 C28 9, 30 5, 29 1 C23 0, 18 3, 16 8 Z"/>
        </svg>
    </div>

    <div class="fixed top-14 left-2/3 z-30 pointer-events-none bat-flying bat-3 hidden lg:block opacity-50 dark:opacity-65">
        <svg width="18" height="11" viewBox="0 0 32 18" class="fill-neutral-900 dark:fill-[#0B0A0F] stroke-[#F97316] stroke-[0.7]">
            <path d="M16 8 C14 3, 9 0, 3 1 C2 5, 4 9, 8 12 C5 12, 2 14, 0 17 C5 17, 10 14, 13 13 C14 15, 15 15, 16 15 C17 15, 18 15, 19 13 C22 14, 27 17, 32 17 C30 14, 27 12, 24 12 C28 9, 30 5, 29 1 C23 0, 18 3, 16 8 Z"/>
        </svg>
    </div>
</div>

<style>
/* Spider Dangle & Swing Animation */
.spider-dangle {
    transform-origin: top center;
    animation: spiderSwing 4.5s ease-in-out infinite alternate;
}
@keyframes spiderSwing {
    0% { transform: rotate(10deg); }
    50% { transform: rotate(-8deg); }
    100% { transform: rotate(9deg); }
}

.spider-eye-pulse {
    animation: eyePulse 1.8s ease-in-out infinite alternate;
}
@keyframes eyePulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

/* Flying Bats Animations */
.bat-flying {
    animation-timing-function: ease-in-out;
    animation-iteration-count: infinite;
}
.bat-1 {
    animation: batFloat1 7s ease-in-out infinite alternate;
}
.bat-2 {
    animation: batFloat2 9s ease-in-out infinite alternate;
}
.bat-3 {
    animation: batFloat3 6s ease-in-out infinite alternate;
}

@keyframes batFloat1 {
    0% { transform: translate3d(0, 0, 0) scale(1) rotate(-4deg); }
    25% { transform: translate3d(45px, 10px, 0) scale(1.08) rotate(6deg); }
    50% { transform: translate3d(-30px, 18px, 0) scale(0.92) rotate(-8deg); }
    75% { transform: translate3d(55px, 6px, 0) scale(1.1) rotate(5deg); }
    100% { transform: translate3d(15px, 0, 0) scale(1) rotate(-3deg); }
}

@keyframes batFloat2 {
    0% { transform: translate3d(0, 0, 0) scale(0.9) rotate(5deg); }
    50% { transform: translate3d(-45px, 14px, 0) scale(1.02) rotate(-7deg); }
    100% { transform: translate3d(35px, -8px, 0) scale(0.88) rotate(6deg); }
}

@keyframes batFloat3 {
    0% { transform: translate3d(0, 0, 0) rotate(-5deg); }
    50% { transform: translate3d(35px, 8px, 0) rotate(8deg); }
    100% { transform: translate3d(-20px, 4px, 0) rotate(-4deg); }
}

/* Castle Window Flickers */
.window-flicker-1 {
    animation: flicker1 2.2s infinite ease-in-out alternate;
}
.window-flicker-2 {
    animation: flicker2 1.8s infinite ease-in-out alternate;
}
.window-flicker-3 {
    animation: flicker3 2.7s infinite ease-in-out alternate;
}
@keyframes flicker1 {
    0% { opacity: 0.95; }
    30% { opacity: 0.6; }
    60% { opacity: 1; }
    80% { opacity: 0.75; }
    100% { opacity: 0.9; }
}
@keyframes flicker2 {
    0% { opacity: 0.7; }
    40% { opacity: 1; }
    70% { opacity: 0.55; }
    100% { opacity: 0.85; }
}
@keyframes flicker3 {
    0% { opacity: 1; }
    50% { opacity: 0.65; }
    100% { opacity: 0.95; }
}

/* Star Twinkles */
.star-twinkle-1 { animation: starPulse 2.5s infinite ease-in-out; }
.star-twinkle-2 { animation: starPulse 3.5s infinite ease-in-out 1s; }
.star-twinkle-3 { animation: starPulse 4.2s infinite ease-in-out 2s; }
@keyframes starPulse {
    0%, 100% { opacity: 0.3; transform: scale(0.8); }
    50% { opacity: 1; transform: scale(1.3); }
}

/* Card Pumpkin Watermarks */
.halloween .card-shadow {
    position: relative;
    overflow: hidden;
}
.halloween .card-shadow::after {
    content: '';
    position: absolute;
    bottom: -4px;
    right: 8px;
    width: 60px;
    height: 60px;
    pointer-events: none;
    opacity: 0.12;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath fill='%23F97316' d='M50,15 C45,5 35,5 32,8 C38,14 42,15 45,18 C30,16 10,28 10,55 C10,80 30,92 50,92 C70,92 90,80 90,55 C90,28 70,16 55,18 C58,15 62,14 68,8 C65,5 55,5 50,15 Z'/%3E%3Cpolygon points='32,45 42,48 36,36' fill='%230B0A0F'/%3E%3Cpolygon points='68,45 58,48 64,36' fill='%230B0A0F'/%3E%3Cpolygon points='50,50 45,58 55,58' fill='%230B0A0F'/%3E%3Cpath d='M28,68 Q38,62 50,68 Q62,62 72,68 Q62,78 50,72 Q38,78 28,68 Z' fill='%230B0A0F'/%3E%3C/svg%3E");
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 0;
}
.halloween .card-shadow:hover::after {
    opacity: 0.28;
    transform: scale(1.12) rotate(4deg);
    filter: drop-shadow(0 0 12px rgba(249, 115, 22, 0.6));
}
</style>
@endif