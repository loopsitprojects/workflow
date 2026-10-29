@if(\App\Services\FeatureManager::isChristmasThemeEnabled())
<div class="christmas-decorations-wrapper pointer-events-none select-none overflow-hidden" x-show="$store.theme.christmas" x-cloak>
    {{-- Full-Viewport Atmospheric Winter Wonderland Background Scene --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden christmas-backdrop">
        {{-- Dark Mode Arctic Midnight, Aurora Borealis & Snowy Pine Forest Scene --}}
        <div class="hidden dark:block absolute inset-0 w-full h-full opacity-40 transition-opacity duration-500">
            <svg class="w-full h-full object-cover" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <!-- Aurora Borealis Gradient -->
                    <linearGradient id="auroraGrad" x1="0%" y1="0%" x2="100%" y2="80%">
                        <stop offset="0%" stop-color="#10B981" stop-opacity="0.18"/>
                        <stop offset="30%" stop-color="#38BDF8" stop-opacity="0.22"/>
                        <stop offset="70%" stop-color="#818CF8" stop-opacity="0.15"/>
                        <stop offset="100%" stop-color="#06131A" stop-opacity="0"/>
                    </linearGradient>
                    <!-- North Star Glow -->
                    <filter id="starGlow" x="-50%" y="-50%" width="200%" height="200%">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="6" result="glow"/>
                        <feMerge>
                            <feMergeNode in="glow"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                </defs>

                {{-- Twinkling Winter Starfield --}}
                <g class="winter-stars opacity-75">
                    <circle cx="100" cy="80" r="1.3" fill="#FFFFFF" class="star-twinkle-1"/>
                    <circle cx="230" cy="130" r="1" fill="#BAE6FD" class="star-twinkle-2"/>
                    <circle cx="390" cy="65" r="1.4" fill="#FFFFFF" class="star-twinkle-3"/>
                    <circle cx="560" cy="110" r="1.1" fill="#FDE68A" class="star-twinkle-1"/>
                    <circle cx="710" cy="75" r="1.5" fill="#FFFFFF" class="star-twinkle-2"/>
                    <circle cx="890" cy="125" r="1.2" fill="#BAE6FD" class="star-twinkle-3"/>
                    <circle cx="1020" cy="70" r="1.4" fill="#FFFFFF" class="star-twinkle-1"/>
                    <circle cx="1320" cy="110" r="1.2" fill="#FFFFFF" class="star-twinkle-2"/>
                    <circle cx="150" cy="210" r="1" fill="#BAE6FD" class="star-twinkle-3"/>
                    <circle cx="480" cy="230" r="1.3" fill="#FFFFFF" class="star-twinkle-1"/>
                </g>

                {{-- Undulating Aurora Borealis (Northern Lights) --}}
                <path d="M-50 180 Q 250 80 600 160 Q 950 240 1200 110 Q 1380 20 1500 140 L 1500 0 L -50 0 Z"
                      fill="url(#auroraGrad)" class="aurora-wave-1"/>
                <path d="M-50 220 Q 300 120 700 210 Q 1050 150 1350 220 L 1500 200 L 1500 0 L -50 0 Z"
                      fill="url(#auroraGrad)" opacity="0.7" class="aurora-wave-2"/>

                {{-- Brilliant Glowing North Star / Bethlehem Star (Upper Right) --}}
                <g class="north-star" transform="translate(1220, 140)">
                    <!-- Outer radiant diamond rays -->
                    <path d="M0 -32 L3 -6 L28 0 L3 6 L0 32 L-3 6 L-28 0 L-3 -6 Z" fill="#FFFFFF" filter="url(#starGlow)" opacity="0.95" class="star-ray-pulse"/>
                    <path d="M0 -18 L2 -3 L16 0 L2 3 L0 18 L-2 3 L-16 0 L-2 -3 Z" fill="#FDE68A" />
                    <!-- Diagonal subtle rays -->
                    <path d="M-12 -12 L0 -2 L12 -12 L2 0 L12 12 L0 2 L-12 12 L-2 0 Z" fill="#BAE6FD" opacity="0.75" />
                    <circle cx="0" cy="0" r="3.5" fill="#FFFFFF" />
                </g>

                {{-- Distant Snow-Capped Mountain Range --}}
                <g class="mountains" opacity="0.65">
                    <!-- Far Mountain Silhouettes -->
                    <polygon points="-50,650 180,480 380,630 650,420 950,650 1250,460 1500,640 1500,900 -50,900" fill="#0A1E2B" />
                    <!-- Snow caps on mountain peaks -->
                    <polygon points="180,480 140,525 170,515 180,535 200,518 220,530" fill="#38BDF8" opacity="0.45" />
                    <polygon points="650,420 600,480 635,465 650,490 670,470 705,485" fill="#38BDF8" opacity="0.5" />
                    <polygon points="1250,460 1210,510 1235,500 1250,520 1275,505 1295,515" fill="#38BDF8" opacity="0.45" />
                </g>

                {{-- Evergreen Pine Forest Silhouettes along Horizon --}}
                <g class="pine-forest" transform="translate(0, 680)">
                    <!-- Rolling Snowdrifts Base -->
                    <path d="M0 80 Q 320 30 700 70 Q 1050 110 1440 50 L 1440 220 L 0 220 Z" fill="#081822"/>
                    <path d="M0 120 Q 450 70 950 115 Q 1250 85 1440 100 L 1440 220 L 0 220 Z" fill="#051017"/>

                    <!-- Pine Trees of Varying Heights -->
                    <!-- Cluster 1 (Left) -->
                    <polygon points="30,80 45,20 60,80" fill="#0B2433"/>
                    <polygon points="35,60 45,20 55,60" fill="#081E2B"/>
                    <polygon points="50,85 70,10 90,85" fill="#09202E"/>
                    <polygon points="80,90 95,35 110,90" fill="#0B2433"/>
                    <polygon points="120,80 140,25 160,80" fill="#081E2B"/>

                    <!-- Cluster 2 (Mid-left) -->
                    <polygon points="340,75 360,15 380,75" fill="#0B2433"/>
                    <polygon points="370,80 390,30 410,80" fill="#081E2B"/>
                    <polygon points="420,85 435,40 450,85" fill="#09202E"/>

                    <!-- Cluster 3 (Mid-right) -->
                    <polygon points="880,80 905,10 930,80" fill="#09202E"/>
                    <polygon points="920,85 940,30 960,85" fill="#0B2433"/>
                    <polygon points="950,90 970,45 990,90" fill="#081E2B"/>

                    <!-- Cluster 4 (Right) -->
                    <polygon points="1260,75 1285,15 1310,75" fill="#09202E"/>
                    <polygon points="1300,80 1320,25 1340,80" fill="#0B2433"/>
                    <polygon points="1330,85 1350,35 1370,85" fill="#081E2B"/>
                    <polygon points="1380,80 1405,10 1430,80" fill="#09202E"/>
                </g>
            </svg>
        </div>

        {{-- Light Mode Winter Wonderland / Snowy Rolling Hills Scene --}}
        <div class="block dark:hidden absolute inset-0 w-full h-full opacity-20 transition-opacity duration-500">
            <svg class="w-full h-full object-cover" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Soft Winter Sun Halo -->
                <circle cx="1220" cy="150" r="100" fill="#BAE6FD" opacity="0.3"/>
                <circle cx="1220" cy="150" r="50" fill="#E0F2FE" opacity="0.6"/>
                <!-- Rolling Snowdrifts -->
                <path d="M0 690 Q 350 630 750 680 Q 1100 730 1440 650 L 1440 900 L 0 900 Z" fill="#BAE6FD" opacity="0.3"/>
                <path d="M0 760 Q 420 710 880 770 Q 1200 720 1440 750 L 1440 900 L 0 900 Z" fill="#7DD3FC" opacity="0.2"/>
                <!-- Snow Pine Silhouettes in Forest Green -->
                <g transform="translate(0, 680)" opacity="0.35">
                    <polygon points="50,90 70,10 90,90" fill="#047857"/>
                    <polygon points="80,95 95,35 110,95" fill="#059669"/>
                    <polygon points="120,85 140,25 160,85" fill="#047857"/>
                    <polygon points="880,85 905,10 930,85" fill="#047857"/>
                    <polygon points="920,90 940,30 960,90" fill="#059669"/>
                    <polygon points="1280,80 1305,15 1330,80" fill="#047857"/>
                    <polygon points="1350,85 1375,20 1400,85" fill="#059669"/>
                </g>
            </svg>
        </div>
    </div>

    {{-- Top-left Corner Festive Holly Leaves & Berries --}}
    <div class="fixed top-0 left-0 z-40 pointer-events-none w-26 h-26 opacity-75">
        <svg viewBox="0 0 100 100" class="w-full h-full filter drop-shadow-[0_2px_10px_rgba(239,68,68,0.4)]">
            <!-- Holly Leaf 1 (Left) -->
            <path d="M 0,0 C 15,10 20,25 15,40 C 25,35 40,42 45,35 C 38,25 45,10 35,0 Z"
                  fill="#047857" class="dark:fill-[#065F46]" stroke="#10B981" stroke-width="0.8"/>
            <path d="M 5,5 Q 25,20 40,30" stroke="#34D399" stroke-width="0.8" fill="none" opacity="0.6"/>
            <!-- Holly Leaf 2 (Right) -->
            <path d="M 0,0 C 10,15 25,20 40,15 C 35,25 42,40 35,45 C 25,38 10,45 0,35 Z"
                  fill="#059669" class="dark:fill-[#047857]" stroke="#34D399" stroke-width="0.8"/>
            <path d="M 5,5 Q 20,25 30,40" stroke="#6EE7B7" stroke-width="0.8" fill="none" opacity="0.6"/>
            <!-- Red Berries with glossy highlight -->
            <circle cx="28" cy="28" r="5.5" fill="#DC2626" class="dark:fill-[#EF4444]"/>
            <circle cx="26.5" cy="26.5" r="1.5" fill="#FECACA" opacity="0.9"/>
            <circle cx="37" cy="24" r="5" fill="#B91C1C" class="dark:fill-[#DC2626]"/>
            <circle cx="35.5" cy="22.5" r="1.3" fill="#FECACA" opacity="0.9"/>
            <circle cx="27" cy="38" r="4.8" fill="#991B1B" class="dark:fill-[#B91C1C]"/>
            <circle cx="25.5" cy="36.5" r="1.2" fill="#FECACA" opacity="0.9"/>
        </svg>
    </div>

    {{-- Dangling Swaying Christmas Bauble from Navbar --}}
    <div class="fixed top-0 left-36 z-50 pointer-events-auto cursor-pointer group bauble-dangle christmas-bauble">
        <div class="bauble-thread w-[1px] bg-amber-400/90 dark:bg-amber-300/80 h-9 mx-auto origin-top transition-all duration-300 group-hover:h-14"></div>
        <div class="bauble-body relative -mt-0.5">
            <svg width="26" height="30" viewBox="0 0 24 28" class="filter drop-shadow-[0_2px_12px_rgba(239,68,68,0.6)]">
                <!-- Cap -->
                <rect x="9.5" y="1" width="5" height="3" rx="1" fill="#FBBF24" stroke="#D97706" stroke-width="0.6"/>
                <path d="M 12,1 L 12,0" stroke="#FBBF24" stroke-width="1.2"/>
                <!-- Bauble Sphere -->
                <circle cx="12" cy="15" r="9.5" fill="url(#baubleGrad)" stroke="#F87171" stroke-width="0.6"/>
                <!-- Decorative Gold Stars / Band -->
                <path d="M 3.5,14 Q 12,18 20.5,14" stroke="#FDE68A" stroke-width="1.2" fill="none" opacity="0.85"/>
                <path d="M 4,16 Q 12,20 20,16" stroke="#FBBF24" stroke-width="0.8" fill="none" opacity="0.85"/>
                <!-- Little Sparkle -->
                <circle cx="8" cy="10" r="1.8" fill="#FFFFFF" opacity="0.75"/>
                <circle cx="9.5" cy="11.5" r="0.8" fill="#FFFFFF" opacity="0.95"/>
                <!-- Gradient definition -->
                <defs>
                    <radialGradient id="baubleGrad" cx="35%" cy="30%" r="65%">
                        <stop offset="0%" stop-color="#F87171"/>
                        <stop offset="40%" stop-color="#EF4444"/>
                        <stop offset="90%" stop-color="#991B1B"/>
                    </radialGradient>
                </defs>
            </svg>
        </div>
    </div>

    {{-- Gracefully Drifting Falling Snowflakes Across Viewport --}}
    <div class="snowflake-container christmas-snow-layer fixed inset-0 pointer-events-none z-30 overflow-hidden">
        <div class="snowflake sf-1">❄</div>
        <div class="snowflake sf-2">❅</div>
        <div class="snowflake sf-3">❆</div>
        <div class="snowflake sf-4">❄</div>
        <div class="snowflake sf-5">❅</div>
        <div class="snowflake sf-6">❆</div>
        <div class="snowflake sf-7">❄</div>
        <div class="snowflake sf-8">❅</div>
        <div class="snowflake sf-9">❆</div>
        <div class="snowflake sf-10">❄</div>
        <div class="snowflake sf-11">❅</div>
        <div class="snowflake sf-12">❆</div>
    </div>
</div>

<style>
/* Bauble Swing Animation */
.bauble-dangle {
    transform-origin: top center;
    animation: baubleSwing 4.5s ease-in-out infinite alternate;
}
@keyframes baubleSwing {
    0% { transform: rotate(8deg); }
    50% { transform: rotate(-7deg); }
    100% { transform: rotate(7deg); }
}

/* North Star Pulse */
.star-ray-pulse {
    animation: northStarRay 3s ease-in-out infinite alternate;
    transform-origin: center;
}
@keyframes northStarRay {
    0% { transform: scale(0.9) rotate(0deg); opacity: 0.8; }
    50% { transform: scale(1.15) rotate(2deg); opacity: 1; filter: drop-shadow(0 0 10px #FFFFFF); }
    100% { transform: scale(0.95) rotate(-1deg); opacity: 0.85; }
}

/* Aurora Waves */
.aurora-wave-1 {
    animation: auroraWave1 14s ease-in-out infinite alternate;
}
.aurora-wave-2 {
    animation: auroraWave2 18s ease-in-out infinite alternate;
}
@keyframes auroraWave1 {
    0% { transform: translateY(0px) scaleY(1); opacity: 0.55; }
    50% { transform: translateY(15px) scaleY(1.15); opacity: 0.85; }
    100% { transform: translateY(-10px) scaleY(0.95); opacity: 0.6; }
}
@keyframes auroraWave2 {
    0% { transform: translateY(0px) scaleX(1); opacity: 0.45; }
    50% { transform: translateY(-12px) scaleX(1.05); opacity: 0.75; }
    100% { transform: translateY(8px) scaleX(0.98); opacity: 0.5; }
}

/* Snowflake Fall Animations */
.snowflake {
    position: fixed;
    top: -24px;
    user-select: none;
    pointer-events: none;
    z-index: 30;
    line-height: 1;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
}
html.dark.christmas .snowflake {
    color: rgba(248, 250, 252, 0.75);
    text-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
}
html.christmas:not(.dark) .snowflake {
    color: rgba(56, 189, 248, 0.65);
    text-shadow: 0 0 6px rgba(56, 189, 248, 0.35);
}

.sf-1  { left: 6%;  font-size: 16px; animation: snowFall 11s ease-in-out infinite; animation-delay: 0s; }
.sf-2  { left: 14%; font-size: 12px; animation: snowFall 15s ease-in-out infinite; animation-delay: 2.5s; }
.sf-3  { left: 23%; font-size: 18px; animation: snowFall 13s ease-in-out infinite; animation-delay: 4s; }
.sf-4  { left: 34%; font-size: 14px; animation: snowFall 17s ease-in-out infinite; animation-delay: 1.2s; }
.sf-5  { left: 45%; font-size: 20px; animation: snowFall 12s ease-in-out infinite; animation-delay: 5s; }
.sf-6  { left: 56%; font-size: 11px; animation: snowFall 19s ease-in-out infinite; animation-delay: 3s; }
.sf-7  { left: 65%; font-size: 16px; animation: snowFall 14s ease-in-out infinite; animation-delay: 0.5s; }
.sf-8  { left: 74%; font-size: 13px; animation: snowFall 16s ease-in-out infinite; animation-delay: 3.5s; }
.sf-9  { left: 83%; font-size: 19px; animation: snowFall 12s ease-in-out infinite; animation-delay: 6s; }
.sf-10 { left: 91%; font-size: 12px; animation: snowFall 18s ease-in-out infinite; animation-delay: 2s; }
.sf-11 { left: 96%; font-size: 15px; animation: snowFall 15s ease-in-out infinite; animation-delay: 4.5s; }
.sf-12 { left: 50%; font-size: 10px; animation: snowFall 21s ease-in-out infinite; animation-delay: 7s; }

@keyframes snowFall {
    0% {
        top: -24px;
        transform: translateX(0px) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 0.85;
    }
    50% {
        transform: translateX(28px) rotate(180deg);
    }
    90% {
        opacity: 0.85;
    }
    100% {
        top: 105vh;
        transform: translateX(-18px) rotate(360deg);
        opacity: 0;
    }
}
</style>
@endif