<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TereLearn — Education that Transcends</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --palette-marigold: #fadd7d;
      --palette-wood: #291304;
      --palette-salomie: #fbd990;
      --palette-chardonnay: #facb8c;
      --palette-shingle: #6c543c;
      --green-50: #fbd990;
      --green-100: #fadd7d;
      --green-200: #facb8c;
      --green-300: #fbd990;
      --green-400: #fadd7d;
      --green-500: #facb8c;
      --green-600: #6c543c;
      --green-700: #6c543c;
      --green-800: #291304;
      --green-900: #291304;
      --text-primary: #291304;
      --text-secondary: #6c543c;
      --text-muted: #8a7258;
      --border: #fbd990;
      --bg: #fff8e8;
      --white: #ffffff;
      --radius-sm: 8px;
      --radius: 16px;
      --radius-lg: 24px;
      --shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
      --shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.04);
      --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.04);
      --shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.15);
      --shadow-glow: 0 0 40px rgba(250,203,140,0.28);
    }
        /* Hide Edge/IE built-in password reveal button */
    input::-ms-reveal,
    input::-ms-clear {
      display: none !important;
    }
    html { scroll-behavior: smooth; }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      overflow-x: hidden;
    }

    /* ═══════════════════════════════════════════
       LANDING PAGE
    ═══════════════════════════════════════════ */

    /* ── NAV ── */
    .lp-nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 48px;
      background: rgba(41, 19, 4, 0.92);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(255,255,255,0.08);
      transition: all 0.3s ease;
    }

    .lp-nav.scrolled {
      padding: 12px 48px;
      background: rgba(41, 19, 4, 0.97);
    }

    .lp-nav-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }

    .lp-nav-brand-icon {
      width: 48px;
      height: 48px;
      min-width: 48px;
      min-height: 48px;
      border-radius: 999px;
      background: transparent;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      flex-shrink: 0;
      position: relative;
      isolation: isolate;
    }

    .lp-nav-brand-icon::before {
      content: '';
      position: absolute;
      inset: -10px;
      border-radius: 50%;
      background:
        radial-gradient(circle, rgba(255,255,255,0.22) 0%, rgba(255,255,255,0.1) 42%, rgba(255,255,255,0.04) 64%, transparent 80%);
      filter: blur(6px);
      z-index: -1;
      pointer-events: none;
    }

    .lp-nav-brand-icon img {
      width: 84%;
      height: 84%;
      object-fit: contain;
      display: block;
    }

    .lp-nav-brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.3rem;
      font-weight: 600;
      color: white;
      letter-spacing: -0.01em;
      line-height: 1;
      display: flex;
      align-items: center;
      padding-top: 1px;
    }

    .lp-nav-links {
      display: flex;
      align-items: center;
      gap: 32px;
    }

    .lp-nav-links a {
      color: rgba(255,255,255,0.75);
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      transition: color 0.2s;
    }

    .lp-nav-links a:hover { color: white; }

    .lp-nav-cta {
      background: white;
      color: var(--green-700) !important;
      padding: 8px 20px;
      border-radius: 50px;
      font-weight: 600 !important;
      transition: all 0.25s ease !important;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }

    .lp-nav-cta:hover {
      background: var(--green-50) !important;
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.2);
    }

    /* ── HERO ── */
    .lp-hero {
      min-height: 100vh;
      background: linear-gradient(145deg, var(--green-900) 0%, var(--green-800) 35%, var(--green-700) 65%, var(--green-600) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
      padding: 120px 48px 80px;
    }

    /* Decorative background shapes */
    .hero-orb {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      filter: blur(80px);
      opacity: 0.15;
    }
    .hero-orb-1 { width: 600px; height: 600px; background: #fadd7d; top: -200px; right: -100px; animation: orbFloat 16s ease-in-out infinite; }
    .hero-orb-2 { width: 400px; height: 400px; background: #facb8c; bottom: -100px; left: -100px; animation: orbFloat 20s ease-in-out infinite reverse; }
    .hero-orb-3 { width: 250px; height: 250px; background: #ffffff; top: 40%; left: 30%; animation: orbFloat 12s ease-in-out infinite 4s; }

    @keyframes orbFloat {
      0%, 100% { transform: translate(0, 0) scale(1); }
      33% { transform: translate(30px, -40px) scale(1.05); }
      66% { transform: translate(-20px, 20px) scale(0.97); }
    }

    /* Grid pattern overlay */
    .hero-grid {
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
      background-size: 60px 60px;
      pointer-events: none;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      display: grid;
      grid-template-columns: 1fr 1fr;
      align-items: center;
      gap: 80px;
      max-width: 1200px;
      width: 100%;
    }

    .hero-text { animation: heroFadeLeft 1s cubic-bezier(0.23,1,0.32,1) both; }
    .hero-visual { animation: heroFadeRight 1s cubic-bezier(0.23,1,0.32,1) 0.2s both; }

    @keyframes heroFadeLeft {
      from { opacity: 0; transform: translateX(-40px); }
      to { opacity: 1; transform: translateX(0); }
    }
    @keyframes heroFadeRight {
      from { opacity: 0; transform: translateX(40px); }
      to { opacity: 1; transform: translateX(0); }
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 50px;
      padding: 6px 14px 6px 8px;
      font-size: 0.78rem;
      font-weight: 600;
      color: rgba(255,255,255,0.9);
      letter-spacing: 0.04em;
      text-transform: uppercase;
      margin-bottom: 24px;
      backdrop-filter: blur(8px);
    }

    .hero-badge-dot {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: linear-gradient(135deg, #fadd7d, #facb8c);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 10px;
    }

    .hero-title {
      font-family: 'DM Serif Display', serif;
      font-size: clamp(2.8rem, 5vw, 4.2rem);
      font-weight: 400;
      color: white;
      line-height: 1.1;
      letter-spacing: -0.02em;
      margin-bottom: 20px;
    }

    .hero-title em {
      font-style: italic;
      color: #fadd7d;
    }

    .hero-subtitle {
      font-size: 1.05rem;
      color: rgba(255,255,255,0.65);
      line-height: 1.7;
      max-width: 480px;
      margin-bottom: 40px;
      font-weight: 400;
    }

    .hero-actions {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .btn-hero-primary {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: white;
      color: var(--green-800);
      padding: 14px 28px;
      border-radius: 50px;
      font-weight: 700;
      font-size: 0.95rem;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.23,1,0.32,1);
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    .btn-hero-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 32px rgba(0,0,0,0.3);
      background: #fbd990;
    }

    .btn-hero-primary svg {
      width: 16px;
      height: 16px;
      fill: none;
      stroke: var(--green-700);
      stroke-width: 2.5;
      stroke-linecap: round;
      stroke-linejoin: round;
      transition: transform 0.3s;
    }

    .btn-hero-primary:hover svg { transform: translateX(3px); }

    .btn-hero-ghost {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: rgba(255,255,255,0.8);
      font-weight: 600;
      font-size: 0.9rem;
      text-decoration: none;
      padding: 14px 4px;
      border-bottom: 1px solid rgba(255,255,255,0.3);
      transition: all 0.25s;
    }

    .btn-hero-ghost:hover {
      color: white;
      border-color: white;
    }

    /* Hero stats */
    .hero-stats {
      display: flex;
      gap: 32px;
      margin-top: 48px;
      padding-top: 32px;
      border-top: 1px solid rgba(255,255,255,0.12);
    }

    .hero-stat-value {
      font-family: 'DM Serif Display', serif;
      font-size: 1.8rem;
      color: white;
      line-height: 1;
      margin-bottom: 4px;
    }

    .hero-stat-label {
      font-size: 0.78rem;
      color: rgba(255,255,255,0.5);
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }

    /* Hero visual card */
    .hero-visual {
      display: flex;
      justify-content: flex-end;
      position: relative;
    }

    .hero-card-wrap {
      position: relative;
      width: 100%;
      max-width: 460px;
    }

    /* Student image */
    .hero-student-img {
      position: absolute;
      bottom: -10px;
      left: -60px;
      width: 220px;
      z-index: 3;
      filter: drop-shadow(0 20px 40px rgba(0,0,0,0.35));
      animation: studentFloat 6s ease-in-out infinite;
    }

    @keyframes studentFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-12px); }
    }

    /* Mockup card */
    .hero-mockup {
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: var(--radius-lg);
      padding: 28px;
      position: relative;
      z-index: 2;
      box-shadow: 0 40px 80px rgba(0,0,0,0.3);
    }

    .mockup-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .mockup-title {
      font-family: 'Playfair Display', serif;
      color: white;
      font-size: 1rem;
      font-weight: 600;
    }

    .mockup-dots {
      display: flex;
      gap: 6px;
    }

    .mockup-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      opacity: 0.4;
    }

    .mockup-dot:nth-child(1) { background: #ff5f57; }
    .mockup-dot:nth-child(2) { background: #ffbd2e; }
    .mockup-dot:nth-child(3) { background: #28c840; }

    .mockup-chart-area {
      background: rgba(255,255,255,0.05);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 16px;
    }

    .chart-label {
      font-size: 0.72rem;
      color: rgba(255,255,255,0.5);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 12px;
      font-weight: 600;
    }

    .cluster-bars {
      display: flex;
      gap: 8px;
      align-items: flex-end;
      height: 80px;
    }

    .cluster-bar-group {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      flex: 1;
    }

    .cluster-bar {
      width: 100%;
      border-radius: 4px;
      position: relative;
      overflow: hidden;
      transition: height 0.6s cubic-bezier(0.23,1,0.32,1);
    }

    .cluster-bar::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(255,255,255,0.15) 0%, transparent 100%);
    }

    .cluster-bar.high { height: 72px; background: linear-gradient(180deg, #fadd7d, #6c543c); }
    .cluster-bar.mid  { height: 50px; background: linear-gradient(180deg, #facc15, #ca8a04); }
    .cluster-bar.low  { height: 30px; background: linear-gradient(180deg, #f87171, #dc2626); }

    .cluster-tag {
      font-size: 0.6rem;
      font-weight: 700;
      color: rgba(255,255,255,0.5);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .mockup-pills {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .mockup-pill {
      display: flex;
      align-items: center;
      gap: 5px;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 50px;
      padding: 5px 10px;
      font-size: 0.72rem;
      color: rgba(255,255,255,0.7);
      font-weight: 500;
    }

    .pill-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
    }

    .pill-dot.green { background: #fadd7d; }
    .pill-dot.yellow { background: #facc15; }
    .pill-dot.red { background: #f87171; }

    /* Floating notification */
    .hero-notif {
      position: absolute;
      top: -20px;
      right: -20px;
      background: white;
      border-radius: 14px;
      padding: 12px 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.2);
      z-index: 4;
      animation: notifPop 0.6s cubic-bezier(0.23,1,0.32,1) 1s both, notifFloat 5s ease-in-out 2s infinite;
    }

    @keyframes notifPop {
      from { opacity: 0; transform: scale(0.8) translateY(10px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }

    @keyframes notifFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }

    .notif-icon {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: linear-gradient(135deg, #fbd990, #facb8c);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .notif-text { line-height: 1.3; }
    .notif-text strong { font-size: 0.78rem; color: var(--text-primary); font-weight: 700; display: block; }
    .notif-text span { font-size: 0.7rem; color: var(--text-muted); }

    /* ── FEATURES ── */
    .lp-features {
      padding: 100px 48px;
      background: var(--bg);
    }

    .section-header {
      text-align: center;
      max-width: 600px;
      margin: 0 auto 64px;
    }

    .section-eyebrow {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--green-600);
      letter-spacing: 0.12em;
      text-transform: uppercase;
      margin-bottom: 12px;
    }

    .section-title {
      font-family: 'DM Serif Display', serif;
      font-size: clamp(2rem, 3.5vw, 2.8rem);
      color: var(--text-primary);
      line-height: 1.2;
      margin-bottom: 16px;
    }

    .section-title em {
      font-style: italic;
      color: var(--green-600);
    }

    .section-desc {
      font-size: 1rem;
      color: var(--text-secondary);
      line-height: 1.7;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
      max-width: 1100px;
      margin: 0 auto;
    }

    .feature-card {
      background: white;
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 32px;
      position: relative;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.23,1,0.32,1);
    }

    .feature-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, var(--green-50) 0%, transparent 60%);
      opacity: 0;
      transition: opacity 0.3s;
    }

    .feature-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.08);
      border-color: var(--green-200);
    }

    .feature-card:hover::before { opacity: 1; }

    .feature-card.featured {
      background: linear-gradient(135deg, var(--green-800), var(--green-700));
      border-color: transparent;
      color: white;
    }

    .feature-card.featured::before { display: none; }

    .feature-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: var(--green-100);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      position: relative;
      z-index: 1;
      font-size: 22px;
      transition: transform 0.3s;
    }

    .feature-card:hover .feature-icon { transform: scale(1.1) rotate(-3deg); }
    .feature-card.featured .feature-icon { background: rgba(255,255,255,0.15); }

    .feature-name {
      font-weight: 700;
      font-size: 1.05rem;
      color: var(--text-primary);
      margin-bottom: 10px;
      position: relative;
      z-index: 1;
    }

    .feature-card.featured .feature-name { color: white; }

    .feature-desc {
      font-size: 0.88rem;
      color: var(--text-secondary);
      line-height: 1.65;
      position: relative;
      z-index: 1;
    }

    .feature-card.featured .feature-desc { color: rgba(255,255,255,0.7); }

    .feature-tag {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      margin-top: 16px;
      padding: 4px 10px;
      background: var(--green-100);
      color: var(--green-700);
      border-radius: 50px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      position: relative;
      z-index: 1;
    }

    .feature-card.featured .feature-tag {
      background: rgba(255,255,255,0.15);
      color: rgba(255,255,255,0.9);
    }

    /* ── HOW IT WORKS ── */
    .lp-how {
      padding: 100px 48px;
      background: white;
    }

    .how-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 80px;
      align-items: center;
      max-width: 1100px;
      margin: 0 auto;
    }

    .how-steps {
      display: flex;
      flex-direction: column;
      gap: 32px;
    }

    .how-step {
      display: flex;
      gap: 20px;
      align-items: flex-start;
    }

    .step-number {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--green-700), var(--green-600));
      color: white;
      font-weight: 800;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 4px 12px rgba(108,84,60,0.24);
    }

    .step-content { padding-top: 2px; }
    .step-title { font-weight: 700; font-size: 1rem; color: var(--text-primary); margin-bottom: 6px; }
    .step-desc { font-size: 0.88rem; color: var(--text-secondary); line-height: 1.65; }

    /* Roles card stack */
    .roles-stack {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .role-card {
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 18px 20px;
      display: flex;
      align-items: center;
      gap: 14px;
      transition: all 0.3s ease;
      cursor: default;
    }

    .role-card:hover {
      border-color: var(--green-300);
      background: var(--green-50);
      transform: translateX(6px);
      box-shadow: 0 4px 16px rgba(108,84,60,0.12);
    }

    .role-emoji {
      font-size: 1.4rem;
      width: 44px;
      height: 44px;
      background: white;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      flex-shrink: 0;
    }

    .role-info strong { font-size: 0.9rem; font-weight: 700; color: var(--text-primary); display: block; margin-bottom: 2px; }
    .role-info span { font-size: 0.8rem; color: var(--text-secondary); }

    /* ── DEVELOPERS ── */
    .lp-developers {
      padding: 56px 48px 40px;
      min-height: calc(100svh - 88px);
      scroll-margin-top: 88px;
      background:
        radial-gradient(circle at top left, rgba(250,203,140,0.24), transparent 34%),
        linear-gradient(180deg, #fff8e8 0%, #fbd990 100%);
      overflow: hidden;
      position: relative;
    }

    .lp-developers::before,
    .lp-developers::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      filter: blur(12px);
      opacity: 0.5;
    }

    .lp-developers::before {
      width: 220px;
      height: 220px;
      top: 40px;
      right: -60px;
      background: rgba(250, 221, 125, 0.22);
    }

    .lp-developers::after {
      width: 180px;
      height: 180px;
      bottom: 30px;
      left: -50px;
      background: rgba(250, 203, 140, 0.22);
    }

    .developers-shell {
      max-width: 1160px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
      min-height: calc(100svh - 88px - 96px);
      display: grid;
      grid-template-rows: auto 1fr auto;
      gap: 20px;
    }

    .developers-stage {
      display: grid;
      grid-template-columns: minmax(260px, 320px) 1fr;
      gap: 28px;
      align-items: center;
      min-height: 0;
    }

    .lp-developers .section-header {
      max-width: 860px;
      margin: 0 auto;
      padding-top: 4px;
      margin-bottom: 0;
    }

    .lp-developers .section-title {
      font-size: clamp(2.2rem, 4vw, 3.5rem);
      margin-bottom: 12px;
    }

    .lp-developers .section-desc {
      max-width: 760px;
      margin: 0 auto;
      font-size: 0.98rem;
      line-height: 1.65;
    }

    .developer-detail {
      background: rgba(255,255,255,0.86);
      border: 1px solid rgba(255,255,255,0.75);
      box-shadow: var(--shadow-xl);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      transition: transform 0.55s cubic-bezier(0.23,1,0.32,1), box-shadow 0.55s ease, opacity 0.38s ease;
    }

    .developers-sidebar {
      padding: 0;
      min-height: clamp(320px, 46vh, 500px);
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
      order: 1;
    }

    .developer-detail::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(165deg, rgba(250,203,140,0.24) 0%, rgba(255,255,255,0) 58%);
      pointer-events: none;
    }

    .developers-sidebar-head,
    .developer-detail-layout,
    .developer-detail-layout > * {
      position: relative;
      z-index: 1;
    }

    .dev-spotlight-portrait {
      position: relative;
      z-index: 1;
      width: min(100%, 320px);
      aspect-ratio: 4 / 5;
      min-height: 340px;
      border-radius: 36px;
      margin: 0;
      background: linear-gradient(135deg, var(--green-700), var(--green-400));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-family: 'DM Serif Display', serif;
      font-size: clamp(3.2rem, 6vh, 4.5rem);
      letter-spacing: 0.05em;
      box-shadow: 0 24px 50px rgba(108,84,60,0.24);
      transition: transform 0.45s cubic-bezier(0.23,1,0.32,1), box-shadow 0.45s ease;
      overflow: hidden;
      isolation: isolate;
    }

    .dev-spotlight-portrait.has-photo {
      background: #e5e7eb !important;
      color: transparent;
    }

    .dev-spotlight-photo {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0;
      transition: opacity 0.35s ease, transform 0.45s cubic-bezier(0.23,1,0.32,1);
      transform: scale(1.02);
      pointer-events: none;
    }

    .dev-spotlight-portrait.has-photo .dev-spotlight-photo {
      opacity: 1;
    }

    .developer-detail.is-animating .dev-spotlight-photo {
      transform: scale(1.06);
    }

    .developer-detail.is-animating .dev-spotlight-portrait {
      transform: scale(1.03);
      box-shadow: 0 30px 58px rgba(108,84,60,0.30);
    }

    .developers-sidebar.is-switching,
    .developer-detail.is-switching {
      transform: translateY(4px);
      box-shadow: 0 12px 28px rgba(15,23,42,0.08);
    }

    .developers-sidebar > *,
    .developer-detail > * {
      transition: opacity 0.28s ease, transform 0.42s cubic-bezier(0.23,1,0.32,1), filter 0.28s ease;
      will-change: opacity, transform;
    }

    .developers-sidebar.is-switching > *,
    .developer-detail.is-switching > * {
      opacity: 0;
      transform: translateY(18px);
      filter: blur(4px);
    }

    .developer-detail {
      border-radius: 32px;
      padding: 24px;
      min-height: clamp(500px, 64vh, 620px);
      position: relative;
      overflow: hidden;
      order: 2;
    }

    .developer-detail-layout {
      display: grid;
      grid-template-columns: minmax(260px, 340px) 1fr;
      gap: 24px;
      align-items: stretch;
      min-height: 100%;
    }

    .developer-visual-column {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .developer-copy-column {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 100%;
    }

    .dev-detail-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 14px;
    }

    .dev-detail-title {
      font-family: 'DM Serif Display', serif;
      font-size: clamp(1.8rem, 2.7vw, 2.5rem);
      color: var(--text-primary);
      line-height: 1.08;
      margin-bottom: 8px;
    }

    .dev-detail-role {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 999px;
      background: var(--green-50);
      color: var(--green-700);
      font-size: 0.82rem;
      font-weight: 700;
    }

    .dev-detail-text {
      color: var(--text-secondary);
      font-size: 0.92rem;
      line-height: 1.68;
      max-width: 640px;
    }

    .dev-skills {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 14px;
    }

    .dev-skill-chip {
      padding: 9px 14px;
      border-radius: 999px;
      background: white;
      border: 1px solid var(--border);
      color: var(--text-primary);
      font-size: 0.82rem;
      font-weight: 600;
      box-shadow: var(--shadow-sm);
    }

    .dev-detail-footer {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
      margin-top: 14px;
    }

    .dev-stat-card {
      background: white;
      border: 1px solid var(--border);
      border-radius: 20px;
      padding: 14px;
      box-shadow: var(--shadow-sm);
    }

    .dev-stat-label {
      color: var(--text-muted);
      font-size: 0.74rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 8px;
    }

    .dev-stat-value {
      color: var(--text-primary);
      font-size: 0.95rem;
      font-weight: 700;
      line-height: 1.45;
    }

    .developers-status {
      color: var(--green-700);
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    .developers-track {
      display: flex;
      flex-direction: column;
      gap: 10px;
      overflow: visible;
    }

    .developer-mini-card {
      border: 0;
      text-align: left;
      cursor: pointer;
      background: white;
      border-radius: 24px;
      padding: 14px;
      border: 1px solid transparent;
      box-shadow: var(--shadow-sm);
      display: grid;
      grid-template-columns: 56px 1fr;
      gap: 12px;
      align-items: center;
      transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease, background 0.28s ease, padding-left 0.28s ease;
    }

    .developer-mini-card:hover {
      transform: translateX(4px);
      box-shadow: var(--shadow);
      border-color: rgba(250,203,140,0.55);
    }

    .developer-mini-card.active {
      background: linear-gradient(180deg, #ffffff 0%, #fbd990 100%);
      border-color: rgba(250,203,140,0.70);
      box-shadow: 0 16px 32px rgba(108,84,60,0.14);
      padding-left: 18px;
    }

    .dev-mini-avatar {
      width: 56px;
      height: 56px;
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-family: 'DM Serif Display', serif;
      font-size: 1.2rem;
      margin-bottom: 0;
      box-shadow: 0 12px 24px rgba(15,23,42,0.15);
      overflow: hidden;
      position: relative;
      flex-shrink: 0;
    }

    .dev-mini-photo {
      display: none;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .developer-mini-card.has-photo .dev-mini-photo {
      display: block;
    }

    .developer-mini-card.has-photo .dev-mini-avatar span {
      display: none;
    }

    .dev-mini-copy {
      min-width: 0;
    }

    .dev-mini-name {
      color: var(--text-primary);
      font-size: 0.88rem;
      font-weight: 700;
      margin-bottom: 4px;
      line-height: 1.4;
    }

    .dev-mini-role {
      color: var(--text-secondary);
      font-size: 0.74rem;
      line-height: 1.5;
    }

    /* ── CTA SECTION ── */
    .lp-cta {
      padding: 100px 48px;
      background: linear-gradient(135deg, var(--green-800) 0%, var(--green-700) 50%, var(--green-600) 100%);
      position: relative;
      overflow: hidden;
      text-align: center;
    }

    .lp-cta::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.04) 0%, transparent 40%);
      pointer-events: none;
    }

    .cta-title {
      font-family: 'DM Serif Display', serif;
      font-size: clamp(2.2rem, 4vw, 3.2rem);
      color: white;
      line-height: 1.15;
      margin-bottom: 16px;
      position: relative;
      z-index: 1;
    }

    .cta-title em { font-style: italic; color: #fadd7d; }

    .cta-sub {
      font-size: 1rem;
      color: rgba(255,255,255,0.65);
      margin-bottom: 40px;
      position: relative;
      z-index: 1;
    }

    .cta-btns {
      display: flex;
      gap: 14px;
      justify-content: center;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
    }

    .btn-cta-white {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: white;
      color: var(--green-800);
      padding: 15px 32px;
      border-radius: 50px;
      font-weight: 700;
      font-size: 0.95rem;
      text-decoration: none;
      transition: all 0.3s cubic-bezier(0.23,1,0.32,1);
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

    .btn-cta-white:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }

    /* ── FOOTER ── */
    .lp-footer {
      background: var(--green-900);
      padding: 40px 48px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
    }

    .footer-brand {
      font-family: 'Playfair Display', serif;
      font-size: 1.1rem;
      color: rgba(255,255,255,0.8);
      font-weight: 600;
    }

    .footer-copy {
      font-size: 0.8rem;
      color: rgba(255,255,255,0.35);
    }

    .footer-school {
      font-size: 0.8rem;
      color: rgba(255,255,255,0.45);
    }

    /* ═══════════════════════════════════════════
       SIGN-IN SECTION
    ═══════════════════════════════════════════ */
    #signin {
      display: flex;
      min-height: 100vh;
      overflow: hidden;
      position: relative;
    }

    /* ── LEFT PANEL: Hero ── */
    .left-panel {
      flex: 1;
      min-width: 0;
      background: linear-gradient(145deg, var(--green-800) 0%, var(--green-700) 30%, var(--green-600) 60%, var(--green-500) 100%);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px;
      position: relative;
      overflow: hidden;
    }

    .left-panel::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse at 30% 20%, rgba(255,255,255,0.06) 0%, transparent 50%),
        radial-gradient(ellipse at 70% 80%, rgba(0,0,0,0.08) 0%, transparent 50%);
      pointer-events: none;
    }

    .hero-shape {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      opacity: 0.08;
    }

    .hero-shape-1 { width: 300px; height: 300px; background: rgba(255,255,255,0.4); top: -80px; right: -60px; animation: shapeFloat 10s ease-in-out infinite; }
    .hero-shape-2 { width: 200px; height: 200px; background: rgba(255,255,255,0.25); bottom: 60px; left: -40px; animation: shapeFloat 12s ease-in-out infinite reverse; }
    .hero-shape-3 { width: 100px; height: 100px; background: rgba(255,255,255,0.3); top: 45%; left: 25%; animation: shapeFloat 8s ease-in-out infinite 3s; }
    .hero-shape-4 { width: 150px; height: 150px; background: rgba(255,255,255,0.15); bottom: -40px; right: 20%; animation: shapeFloat 14s ease-in-out infinite 1s; }

    @keyframes shapeFloat {
      0%, 100% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-25px) scale(1.06); }
    }

    .brand-mark {
      width: 110px;
      height: 110px;
      border-radius: 50%;
      background: transparent;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 28px;
      position: relative;
      z-index: 2;
      transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.4s;
      animation: gentlePulse 4s ease-in-out infinite;
      overflow: hidden;
      padding: 0;
    }

    .brand-mark::before {
      content: '';
      position: absolute;
      inset: -30px;
      border-radius: 50%;
      background:
        radial-gradient(circle, rgba(255,255,255,0.24) 0%, rgba(255,255,255,0.12) 38%, rgba(255,255,255,0.04) 62%, transparent 78%);
      filter: blur(10px);
      z-index: -1;
      pointer-events: none;
    }

    .brand-mark:hover {
      transform: scale(1.08) translateY(-2px);
      box-shadow: 0 0 30px rgba(255,255,255,0.15);
    }

    @keyframes gentlePulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.12); }
      50% { box-shadow: 0 0 0 18px rgba(255,255,255,0); }
    }

    .brand-mark img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
      border-radius: 50%;
    }

    .brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 3rem;
      font-weight: 600;
      color: white;
      letter-spacing: -0.02em;
      position: relative;
      z-index: 2;
      line-height: 1.2;
      text-align: center;
    }

    .brand-tag {
      font-size: 0.9rem;
      color: rgba(255,255,255,0.6);
      font-weight: 400;
      margin-top: 10px;
      position: relative;
      z-index: 2;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      text-align: center;
    }

    /* ── RIGHT PANEL: Form ── */
    .right-panel {
      flex: 1;
      min-width: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 48px;
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
      position: relative;
      overflow: hidden;
    }

    .right-panel::before {
      content: '';
      position: absolute;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(250,203,140,0.26) 0%, transparent 70%);
      top: -100px;
      right: -100px;
      filter: blur(60px);
      pointer-events: none;
    }

    .right-panel::after {
      content: '';
      position: absolute;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(250,221,125,0.22) 0%, transparent 70%);
      bottom: -80px;
      left: -60px;
      filter: blur(50px);
      pointer-events: none;
    }

    .form-container {
      width: 100%;
      max-width: 420px;
      position: relative;
      z-index: 2;
      animation: fadeUp 0.8s cubic-bezier(0.23, 1, 0.32, 1);
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .welcome-text {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      font-weight: 600;
      color: var(--text-primary);
      letter-spacing: -0.02em;
      line-height: 1.2;
      margin-bottom: 8px;
    }

    .sub-text {
      font-size: 0.95rem;
      color: var(--text-secondary);
      margin-bottom: 36px;
      font-weight: 400;
    }

    .form-group {
      margin-bottom: 22px;
      position: relative;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.75rem;
      font-weight: 600;
      color: var(--text-secondary);
      letter-spacing: 0.06em;
      text-transform: uppercase;
      transition: color 0.3s ease;
    }

    .form-group:focus-within label { color: var(--green-600); }

    .input-wrap { position: relative; }

    .form-control {
      width: 100%;
      padding: 14px 18px;
      padding-right: 48px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      font-size: 0.95rem;
      font-family: inherit;
      font-weight: 400;
      background: rgba(255, 255, 255, 0.7);
      color: var(--text-primary);
      transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
      outline: none;
      appearance: none;
    }

    .form-control::placeholder { color: var(--text-muted); font-weight: 400; }
    .form-control:hover { border-color: #cbd5e1; background: rgba(255, 255, 255, 0.9); }

    .form-control:focus {
      border-color: var(--green-400);
      background: white;
      box-shadow: 0 0 0 4px rgba(250, 203, 140, 0.28), 0 1px 3px rgba(0,0,0,0.05);
      transform: translateY(-1px);
    }

    .eye-toggle {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0.3;
      transition: all 0.25s ease;
      border-radius: var(--radius-sm);
      padding: 0;
    }

    .eye-toggle:hover { opacity: 0.7; background: rgba(0,0,0,0.03); }
    .eye-toggle:active { transform: translateY(-50%) scale(0.9); }

    .eye-toggle svg {
      width: 18px;
      height: 18px;
      fill: none;
      stroke: var(--text-secondary);
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
      transition: all 0.2s ease;
    }

    .eye-toggle.active { opacity: 0.6; }
    .eye-toggle.active svg { stroke: var(--green-600); }

    .btn-login {
      width: 100%;
      padding: 14px 24px;
      background: linear-gradient(135deg, #291304 0%, #6c543c 58%, #291304 100%);
      background-size: 200% 200%;
      color: white;
      border: none;
      border-radius: var(--radius);
      font-size: 0.95rem;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
      letter-spacing: 0.01em;
      margin-top: 8px;
      box-shadow: 0 4px 14px rgba(108, 84, 60, 0.28);
    }

    .btn-login::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.15) 50%, transparent 100%);
      transform: translateX(-100%);
      transition: transform 0.6s ease;
    }

    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(108, 84, 60, 0.38); background-position: 100% 0; }
    .btn-login:hover::before { transform: translateX(100%); }
    .btn-login:active { transform: translateY(0) scale(0.98); box-shadow: 0 2px 8px rgba(108, 84, 60, 0.28); }
    .btn-login:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }

    .btn-inner { display: flex; align-items: center; justify-content: center; gap: 8px; position: relative; z-index: 1; }

    .ripple {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.3);
      transform: scale(0);
      animation: rippleEffect 0.6s ease-out;
      pointer-events: none;
    }

    @keyframes rippleEffect { to { transform: scale(4); opacity: 0; } }

    .spinner {
      display: inline-block;
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255,255,255,0.3);
      border-top-color: white;
      border-radius: 50%;
      animation: spin 0.7s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .form-footer {
      margin-top: 24px;
      text-align: center;
      font-size: 0.85rem;
      color: var(--text-muted);
      font-weight: 400;
    }

    .form-footer a { color: var(--green-600); font-weight: 600; text-decoration: none; position: relative; transition: color 0.3s; }
    .form-footer a::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 1.5px; background: var(--green-500); transition: width 0.3s ease; }
    .form-footer a:hover { color: var(--green-700); }
    .form-footer a:hover::after { width: 100%; }

    .alert-box {
      display: none;
      padding: 12px 16px;
      border-radius: var(--radius-sm);
      font-size: 0.85rem;
      font-weight: 500;
      margin-bottom: 20px;
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    .alert-box.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .alert-box.success { background: #fbd990; color: #291304; border: 1px solid #facb8c; }

    .otp-step { display: none; }

    .otp-info {
      background: linear-gradient(135deg, #fbd990, #facb8c);
      border: 1px solid #facb8c;
      border-radius: var(--radius);
      padding: 16px;
      font-size: 0.85rem;
      color: var(--green-700);
      margin-bottom: 24px;
      line-height: 1.6;
    }

    .otp-input { text-align: center; font-size: 1.5rem; letter-spacing: 0.4em; font-weight: 700; padding: 16px; font-family: 'Inter', monospace; }

    .resend-row { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; font-size: 0.8rem; }
    .resend-btn { background: none; border: none; cursor: pointer; color: var(--green-600); font-weight: 600; font-size: 0.8rem; padding: 0; transition: all 0.3s; }
    .resend-btn:hover:not(:disabled) { color: var(--green-700); }
    .resend-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .cooldown-timer { color: var(--text-muted); font-weight: 500; }

    .btn-ghost {
      width: 100%;
      padding: 12px;
      background: transparent;
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      font-size: 0.85rem;
      font-weight: 600;
      font-family: inherit;
      color: var(--text-secondary);
      cursor: pointer;
      transition: all 0.3s;
      margin-top: 12px;
    }

    .btn-ghost:hover { border-color: var(--green-400); color: var(--green-600); background: rgba(250, 203, 140, 0.16); }

    /* MODALS */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.4);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      padding: 24px;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .modal-overlay.active { display: flex; opacity: 1; }

    .modal-box {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: var(--radius-lg);
      padding: 40px;
      max-width: 440px;
      width: 100%;
      box-shadow: var(--shadow-xl);
      transform: scale(0.95) translateY(10px);
      transition: transform 0.3s cubic-bezier(0.23, 1, 0.32, 1);
      border: 1px solid rgba(255,255,255,0.6);
    }

    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    .modal-box h3 { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--text-primary); margin-bottom: 8px; font-weight: 600; }
    .modal-box .desc { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 28px; line-height: 1.6; }
    .modal-btns { display: flex; gap: 12px; }
    .modal-btn { flex: 1; padding: 12px 20px; border: none; border-radius: var(--radius); font-weight: 600; font-family: inherit; cursor: pointer; font-size: 0.9rem; transition: all 0.3s; }
    .modal-btn.primary { background: linear-gradient(135deg, #291304, #6c543c); color: white; box-shadow: 0 4px 12px rgba(108, 84, 60, 0.24); }
    .modal-btn.primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(108, 84, 60, 0.34); }
    .modal-btn.secondary { background: var(--bg); color: var(--text-secondary); border: 1.5px solid var(--border); }
    .modal-btn.secondary:hover { border-color: var(--green-400); color: var(--green-600); background: rgba(250, 203, 140, 0.16); }

    .forgot-modal-box {
      max-width: 920px;
      padding: 0;
      overflow: hidden;
      background: rgba(255,255,255,0.98);
    }

    .forgot-modal-grid {
      display: grid;
      grid-template-columns: minmax(260px, 320px) 1fr;
      min-height: 580px;
    }

    .forgot-aside {
      padding: 40px 32px;
      background:
        radial-gradient(circle at top left, rgba(250,221,125,0.24), transparent 38%),
        linear-gradient(160deg, #291304 0%, #6c543c 48%, #facb8c 100%);
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
    }

    .forgot-aside::before,
    .forgot-aside::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.08);
      pointer-events: none;
    }

    .forgot-aside::before {
      width: 180px;
      height: 180px;
      top: -60px;
      right: -60px;
    }

    .forgot-aside::after {
      width: 140px;
      height: 140px;
      bottom: -40px;
      left: -30px;
    }

    .forgot-aside > * { position: relative; z-index: 1; }
    .forgot-kicker {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.16);
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      margin-bottom: 18px;
    }

    .forgot-aside h3 {
      color: white;
      font-size: 2rem;
      line-height: 1.05;
      margin-bottom: 16px;
    }

    .forgot-aside-copy {
      color: rgba(255,255,255,0.78);
      font-size: 0.96rem;
      line-height: 1.7;
      max-width: 260px;
    }

    .forgot-steps {
      display: grid;
      gap: 12px;
      margin-top: 28px;
    }

    .forgot-step-pill {
      display: grid;
      grid-template-columns: 34px 1fr;
      gap: 12px;
      align-items: center;
      padding: 12px 14px;
      border-radius: 18px;
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.12);
      transition: background 0.25s ease, border-color 0.25s ease, transform 0.25s ease;
    }

    .forgot-step-pill.active {
      background: rgba(255,255,255,0.18);
      border-color: rgba(255,255,255,0.2);
      transform: translateX(4px);
    }

    .forgot-step-pill.done {
      background: rgba(250,203,140,0.22);
      border-color: rgba(250,221,125,0.32);
    }

    .forgot-step-number {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255,255,255,0.18);
      font-weight: 700;
      font-size: 0.88rem;
    }

    .forgot-step-title {
      font-size: 0.88rem;
      font-weight: 700;
      color: white;
      margin-bottom: 2px;
    }

    .forgot-step-desc {
      font-size: 0.76rem;
      color: rgba(255,255,255,0.7);
      line-height: 1.45;
    }

    .forgot-side-note {
      margin-top: 28px;
      padding-top: 18px;
      border-top: 1px solid rgba(255,255,255,0.12);
      font-size: 0.78rem;
      color: rgba(255,255,255,0.68);
      line-height: 1.6;
    }

    .forgot-main {
      padding: 36px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: linear-gradient(180deg, rgba(255,255,255,0.98), #f8fafc);
    }

    .forgot-stage {
      display: none;
      animation: stageFade 0.24s ease;
    }

    .forgot-stage.active {
      display: block;
    }

    @keyframes stageFade {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .forgot-stage-title {
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      color: var(--text-primary);
      line-height: 1.1;
      margin-bottom: 10px;
      font-weight: 600;
    }

    .forgot-stage-desc {
      color: var(--text-secondary);
      font-size: 0.95rem;
      line-height: 1.75;
      margin-bottom: 24px;
      max-width: 460px;
    }

    .forgot-status-card {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      padding: 16px 18px;
      border-radius: 18px;
      background: linear-gradient(180deg, #f8fafc, #f1f5f9);
      border: 1px solid #e2e8f0;
      margin-bottom: 22px;
    }

    .forgot-status-icon {
      width: 40px;
      height: 40px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.05rem;
      background: rgba(250,203,140,0.32);
      color: var(--green-700);
      flex-shrink: 0;
    }

    .forgot-status-title {
      font-size: 0.86rem;
      font-weight: 700;
      color: var(--text-primary);
      margin-bottom: 4px;
    }

    .forgot-status-text {
      color: var(--text-secondary);
      font-size: 0.82rem;
      line-height: 1.55;
    }

    .forgot-code-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin-top: 12px;
      margin-bottom: 22px;
      color: var(--text-secondary);
      font-size: 0.82rem;
      flex-wrap: wrap;
    }

    .forgot-inline-btn {
      border: none;
      background: none;
      color: var(--green-700);
      font-weight: 700;
      cursor: pointer;
      padding: 0;
      font-size: 0.82rem;
    }

    .forgot-inline-btn:disabled {
      opacity: 0.45;
      cursor: not-allowed;
    }

    .forgot-otp-input {
      font-size: 1.4rem;
      text-align: center;
      letter-spacing: 0.42em;
      padding-left: 1.2em;
      font-weight: 700;
    }

    .forgot-password-grid {
      display: grid;
      gap: 18px;
    }

    .forgot-helper {
      margin-top: 8px;
      font-size: 0.8rem;
      color: var(--text-secondary);
      line-height: 1.55;
    }

    .forgot-helper strong {
      color: var(--green-700);
    }

    .strength-bar { height: 4px; background: var(--border); border-radius: 2px; margin: 12px 0 8px; overflow: hidden; }
    .strength-fill { height: 100%; width: 0; border-radius: 2px; transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1); }
    .strength-fill.weak { width: 20%; background: #ef4444; }
    .strength-fill.fair { width: 40%; background: #f97316; }
    .strength-fill.good { width: 60%; background: #eab308; }
    .strength-fill.strong { width: 80%; background: #fadd7d; }
    .strength-fill.excellent { width: 100%; background: #facb8c; }
    .strength-label { font-size: 0.78rem; font-weight: 600; margin-bottom: 16px; }

    .req-list { background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 16px; margin-top: 16px; }
    .req-list-title { font-size: 0.78rem; font-weight: 700; color: var(--text-primary); margin-bottom: 12px; }
    .req-item { display: flex; align-items: center; gap: 10px; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 8px; transition: all 0.3s; }
    .req-item:last-child { margin-bottom: 0; }
    .req-check { width: 18px; height: 18px; border-radius: 50%; background: var(--border); display: flex; align-items: center; justify-content: center; font-size: 10px; flex-shrink: 0; transition: all 0.3s; color: white; font-weight: 700; }
    .req-item.met { color: var(--green-600); }
    .req-item.met .req-check { background: var(--green-500); }

    /* ── RESPONSIVE ── */
    @media (max-width: 1024px) {
      .features-grid { grid-template-columns: repeat(2, 1fr); }
      .how-grid { grid-template-columns: 1fr; gap: 48px; }
      .hero-content { grid-template-columns: 1fr; gap: 48px; text-align: center; }
      .hero-subtitle { max-width: 100%; }
      .hero-visual { justify-content: center; }
      .hero-student-img { left: -30px; width: 160px; }
      .hero-actions { justify-content: center; }
      .hero-stats { justify-content: center; }
      .lp-developers {
        padding-top: 72px;
        padding-bottom: 36px;
      }
      .forgot-modal-grid { grid-template-columns: 1fr; }
      .forgot-aside { padding: 32px 28px; }
      .forgot-aside-copy { max-width: none; }
      .forgot-main { padding: 30px 28px; }
      .developers-shell {
        min-height: auto;
        display: block;
      }
      .developers-stage { grid-template-columns: 1fr; }
      .developer-detail-layout { grid-template-columns: 1fr; }
      .developers-sidebar,
      .developer-detail { min-height: auto; order: initial; }
    }

    @media (max-width: 768px) {
      .lp-nav { padding: 14px 24px; }
      .lp-nav-links { display: none; }
      .lp-hero { padding: 100px 24px 60px; }
      .lp-features, .lp-how, .lp-developers, .lp-cta { padding: 72px 24px; }
      .lp-developers {
        min-height: auto;
        scroll-margin-top: 72px;
      }
      .forgot-modal-box { max-width: 100%; }
      .forgot-main { padding: 24px; }
      .forgot-aside { padding: 24px; }
      .forgot-stage-title { font-size: 1.7rem; }
      .forgot-otp-input { letter-spacing: 0.3em; padding-left: 0.9em; }
      .lp-developers .section-header { margin-bottom: 24px; }
      .features-grid { grid-template-columns: 1fr; }
      .lp-footer { padding: 32px 24px; flex-direction: column; text-align: center; }
      .hero-notif { right: 0; top: -16px; }
      .hero-student-img { display: none; }
      .developers-stage { gap: 18px; }
      .developer-detail { order: 1; }
      .developers-sidebar {
        order: 2;
        min-height: auto;
        justify-content: flex-start;
        position: relative;
        bottom: auto;
        z-index: 1;
        margin-top: 0;
        transition: transform 0.28s ease, opacity 0.28s ease;
      }
      .developers-sidebar.mobile-floating {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 20;
        padding: 10px 16px calc(10px + env(safe-area-inset-bottom));
      }
      .developers-sidebar.mobile-floating::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 22px 22px 0 0;
        background: rgba(248, 250, 252, 0.94);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: 0 -10px 30px rgba(15,23,42,0.08);
        border-top: 1px solid rgba(255,255,255,0.72);
        z-index: -1;
      }
      .developer-detail { padding: 24px; border-radius: 24px; }
      .dev-spotlight-portrait {
        width: 100%;
        min-height: 240px;
        font-size: 3.2rem;
      }
      .developer-detail-layout { gap: 16px; }
      .developer-copy-column { gap: 12px; }
      .dev-detail-top { flex-direction: column; align-items: flex-start; }
      .dev-detail-title { font-size: 1.8rem; margin-bottom: 6px; }
      .dev-detail-role { padding: 7px 10px; font-size: 0.78rem; }
      .dev-detail-text { font-size: 0.88rem; line-height: 1.55; }
      .dev-skills { gap: 8px; margin-top: 12px; }
      .dev-skill-chip { padding: 7px 10px; font-size: 0.74rem; }
      .dev-detail-footer { grid-template-columns: 1fr; }
      .dev-stat-card { padding: 12px; }
      .dev-stat-label { margin-bottom: 6px; font-size: 0.68rem; }
      .dev-stat-value { font-size: 0.86rem; line-height: 1.35; }
      .developers-track {
        flex-direction: row;
        gap: 10px;
        overflow-x: auto;
        overflow-y: hidden;
        justify-content: center;
        width: max-content;
        min-width: 100%;
        padding: 2px 0 6px;
        scrollbar-width: none;
        scroll-snap-type: x proximity;
      }
      .developers-track::-webkit-scrollbar { display: none; }
      .developer-mini-card {
        flex: 0 0 auto;
        width: 76px;
        height: 76px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 22px;
        scroll-snap-align: center;
      }
      .developer-mini-card.active {
        padding-left: 0;
        transform: none;
      }
      .developer-mini-card:hover { transform: none; }
      .dev-mini-copy { display: none; }
      .dev-mini-avatar {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        box-shadow: 0 10px 20px rgba(15,23,42,0.12);
      }
      /* Sign-in section mobile */
      #signin {
        flex-direction: column;
        min-height: 100vh;
        overflow-y: auto;
      }
      .left-panel { flex: none; min-height: 35vh; padding: 40px 24px; }
      .brand-name { font-size: 2.2rem; }
      .brand-tag { font-size: 0.8rem; }
      .right-panel { flex: 1; padding: 40px 24px; min-height: 65vh; }
      .form-container { max-width: 100%; }
      .welcome-text { font-size: 1.6rem; }
    }

    @media (max-width: 380px) {
      .lp-hero { padding: 90px 20px 48px; }
      .left-panel { min-height: 30vh; padding: 32px 20px; }
      .brand-name { font-size: 1.8rem; }
      .right-panel { padding: 32px 20px; }
      .welcome-text { font-size: 1.4rem; }
    }

    /* Scroll-reveal animations */
    .reveal {
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 0.7s cubic-bezier(0.23,1,0.32,1), transform 0.7s cubic-bezier(0.23,1,0.32,1);
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.1s; }
    .reveal-delay-2 { transition-delay: 0.2s; }
    .reveal-delay-3 { transition-delay: 0.3s; }
    .reveal-delay-4 { transition-delay: 0.4s; }
    .reveal-delay-5 { transition-delay: 0.5s; }
  </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════
     LANDING PAGE
═══════════════════════════════════════════════════ -->

<!-- NAV -->
<nav class="lp-nav" id="lpNav">
  <a href="#" class="lp-nav-brand">
    <div class="lp-nav-brand-icon">
      <img src="public/assets/img/logo/terelearn-logo-darkmode.png" alt="TereLearn logo">
    </div>
    <span class="lp-nav-brand-name">TereLearn</span>
  </a>
  <div class="lp-nav-links">
    <a href="#features">Features</a>
    <a href="#how">How It Works</a>
    <a href="#developers">Developers</a>
    <a href="#signin" class="lp-nav-cta">Sign In</a>
  </div>
</nav>

<!-- HERO -->
<section class="lp-hero">
  <div class="hero-orb hero-orb-1"></div>
  <div class="hero-orb hero-orb-2"></div>
  <div class="hero-orb hero-orb-3"></div>
  <div class="hero-grid"></div>

  <div class="hero-content">
    <div class="hero-text">
      <div class="hero-badge">
        <div class="hero-badge-dot">🎓</div>
        Colegio de Sta. Teresa de Avila
      </div>
      <h1 class="hero-title">
        Education that<br>
        <em>Transcends</em><br>
        every limit.
      </h1>
      <p class="hero-subtitle">
        TereLearn is CSTA's cloud-based Learning Management System — bringing together courses, analytics, and AI-powered insights to help every student reach their full potential.
      </p>
      <div class="hero-actions">
        <a href="#signin" class="btn-hero-primary">
          Get Started
          <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <a href="#features" class="btn-hero-ghost">Explore features</a>
      </div>
      <div class="hero-stats">
        <div>
          <div class="hero-stat-value">1,500+</div>
          <div class="hero-stat-label">Students</div>
        </div>
        <div>
          <div class="hero-stat-value">4</div>
          <div class="hero-stat-label">Programs</div>
        </div>
        <div>
          <div class="hero-stat-value">K-Means</div>
          <div class="hero-stat-label">Analytics</div>
        </div>
      </div>
    </div>

    <div class="hero-visual">
      <div class="hero-card-wrap">
        <!-- Floating notification -->
        <div class="hero-notif">
          <div class="notif-icon">📊</div>
          <div class="notif-text">
            <strong>Clustering complete!</strong>
            <span>3 student groups identified</span>
          </div>
        </div>

        <!-- Analytics mockup card -->
        <div class="hero-mockup">
          <div class="mockup-header">
            <div class="mockup-title">K-Means Analytics</div>
            <div class="mockup-dots">
              <div class="mockup-dot"></div>
              <div class="mockup-dot"></div>
              <div class="mockup-dot"></div>
            </div>
          </div>
          <div class="mockup-chart-area">
            <div class="chart-label">Student Performance Clusters</div>
            <div class="cluster-bars">
              <div class="cluster-bar-group">
                <div class="cluster-bar high"></div>
                <div class="cluster-tag">High</div>
              </div>
              <div class="cluster-bar-group">
                <div class="cluster-bar mid"></div>
                <div class="cluster-tag">Avg</div>
              </div>
              <div class="cluster-bar-group">
                <div class="cluster-bar low"></div>
                <div class="cluster-tag">At-Risk</div>
              </div>
            </div>
          </div>
          <div class="mockup-pills">
            <div class="mockup-pill"><div class="pill-dot green"></div>High Performers</div>
            <div class="mockup-pill"><div class="pill-dot yellow"></div>Average</div>
            <div class="mockup-pill"><div class="pill-dot red"></div>Needs Support</div>
          </div>
        </div>

        <!-- Student image -->
        <img src="" alt="CSTA Student" class="hero-student-img" onerror="this.style.display='none'">
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="lp-features" id="features">
  <div class="section-header reveal">
    <div class="section-eyebrow">What TereLearn Offers</div>
    <h2 class="section-title">Everything you need,<br><em>all in one place</em></h2>
    <p class="section-desc">From course delivery to AI-powered clustering analytics — TereLearn centralizes CSTA's entire learning ecosystem.</p>
  </div>

  <div class="features-grid">
    <div class="feature-card reveal reveal-delay-1">
      <div class="feature-icon">📚</div>
      <div class="feature-name">Course & Class Management</div>
      <div class="feature-desc">Upload lesson materials, organize classes by department, and manage faculty assignments — all from a single dashboard.</div>
      <div class="feature-tag">✦ Centralized</div>
    </div>
    <div class="feature-card featured reveal reveal-delay-2">
      <div class="feature-icon">🤖</div>
      <div class="feature-name">K-Means Clustering Analytics</div>
      <div class="feature-desc">Automatically groups students into high-performing, average, and at-risk clusters — enabling targeted interventions and personalized support.</div>
      <div class="feature-tag">✦ AI-Powered</div>
    </div>
    <div class="feature-card reveal reveal-delay-3">
      <div class="feature-icon">⚡</div>
      <div class="feature-name">AI Quiz Generation</div>
      <div class="feature-desc">Faculty can generate randomized quizzes instantly using AI — saving time while ensuring assessment fairness and variety.</div>
      <div class="feature-tag">✦ Automated</div>
    </div>
    <div class="feature-card reveal reveal-delay-4">
      <div class="feature-icon">📹</div>
      <div class="feature-name">Live Class via Google Meet</div>
      <div class="feature-desc">Integrated video conferencing for real-time discussions, live lectures, and collaborative sessions — directly within each class workspace.</div>
      <div class="feature-tag">✦ Integrated</div>
    </div>
    <div class="feature-card reveal reveal-delay-5">
      <div class="feature-icon">📈</div>
      <div class="feature-name">Performance Dashboards</div>
      <div class="feature-desc">Visual reports on grades, activity logs, and engagement trends — empowering educators to make data-driven decisions.</div>
      <div class="feature-tag">✦ Data-Driven</div>
    </div>
    <div class="feature-card reveal reveal-delay-1">
      <div class="feature-icon">🔐</div>
      <div class="feature-name">Secure OTP Authentication</div>
      <div class="feature-desc">Two-factor login with OTP-based email verification ensures account security across all user roles — from students to system admins.</div>
      <div class="feature-tag">✦ Secure</div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="lp-how" id="how">
  <div class="how-grid">
    <div>
      <div class="section-eyebrow reveal">How It Works</div>
      <h2 class="section-title reveal" style="text-align:left;margin-bottom:40px;">Designed for every<br><em>role at CSTA</em></h2>
      <div class="how-steps">
        <div class="how-step reveal reveal-delay-1">
          <div class="step-number">1</div>
          <div class="step-content">
            <div class="step-title">Admin sets up accounts</div>
            <div class="step-desc">System Admin creates sub-admin accounts for Deans & Secretaries, who then register faculty and students in bulk.</div>
          </div>
        </div>
        <div class="how-step reveal reveal-delay-2">
          <div class="step-number">2</div>
          <div class="step-content">
            <div class="step-title">Faculty prepares classes</div>
            <div class="step-desc">Professors upload lessons, generate AI quizzes, create activities, and schedule Google Meet sessions.</div>
          </div>
        </div>
        <div class="how-step reveal reveal-delay-3">
          <div class="step-number">3</div>
          <div class="step-content">
            <div class="step-title">Students engage & learn</div>
            <div class="step-desc">Students access materials, submit assessments, join live classes, and track their own academic progress.</div>
          </div>
        </div>
        <div class="how-step reveal reveal-delay-4">
          <div class="step-number">4</div>
          <div class="step-content">
            <div class="step-title">Analytics guide decisions</div>
            <div class="step-desc">K-Means clustering auto-segments learners so teachers and deans can identify who needs support — before it's too late.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="roles-stack reveal reveal-delay-2">
      <div class="role-card">
        <div class="role-emoji">🏛️</div>
        <div class="role-info">
          <strong>System Admin</strong>
          <span>Manages accounts, analytics & system-wide settings</span>
        </div>
      </div>
      <div class="role-card">
        <div class="role-emoji">👩‍💼</div>
        <div class="role-info">
          <strong>Dean / Secretary</strong>
          <span>Oversees faculty, students & departmental reports</span>
        </div>
      </div>
      <div class="role-card">
        <div class="role-emoji">👨‍🏫</div>
        <div class="role-info">
          <strong>Faculty / Professor</strong>
          <span>Manages classes, materials, quizzes & grades</span>
        </div>
      </div>
      <div class="role-card">
        <div class="role-emoji">🎓</div>
        <div class="role-info">
          <strong>Student</strong>
          <span>Accesses lessons, submits work & tracks progress</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DEVELOPERS -->
<section class="lp-developers" id="developers">
  <div class="developers-shell">
    <div class="section-header reveal">
      <div class="section-eyebrow">Meet the Developers</div>
      <h2 class="section-title">Built by a team that turns<br><em>ideas into working systems</em></h2>
      <p class="section-desc">This section highlights the people behind TereLearn, with a live spotlight that automatically rotates and expands each contributor's role when selected.</p>
    </div>

    <div class="developers-stage">
      <aside class="developers-sidebar reveal reveal-delay-1">
        <div class="developers-track" id="developersTrack">
          <button type="button" class="developer-mini-card active" data-dev-index="0">
            <div class="dev-mini-avatar" style="background: linear-gradient(135deg, #291304, #fadd7d);"><img src="" alt="" class="dev-mini-photo"><span>RL</span></div>
            <div class="dev-mini-copy">
              <div class="dev-mini-name">Renwel Lucero</div>
              <div class="dev-mini-role">Lead Developer</div>
            </div>
          </button>
          <button type="button" class="developer-mini-card" data-dev-index="1">
            <div class="dev-mini-avatar" style="background: linear-gradient(135deg, #291304, #facb8c);"><img src="" alt="" class="dev-mini-photo"><span>LS</span></div>
            <div class="dev-mini-copy">
              <div class="dev-mini-name">Larry Salva</div>
              <div class="dev-mini-role">System Analyst</div>
            </div>
          </button>
          <button type="button" class="developer-mini-card" data-dev-index="2">
            <div class="dev-mini-avatar" style="background: linear-gradient(135deg, #6c543c, #fbd990);"><img src="" alt="" class="dev-mini-photo"><span>JM</span></div>
            <div class="dev-mini-copy">
              <div class="dev-mini-name">Jhun Rachell Mondido</div>
              <div class="dev-mini-role">QA Tester</div>
            </div>
          </button>
          <button type="button" class="developer-mini-card" data-dev-index="3">
            <div class="dev-mini-avatar" style="background: linear-gradient(135deg, #6c543c, #facb8c);"><img src="" alt="" class="dev-mini-photo"><span>KL</span></div>
            <div class="dev-mini-copy">
              <div class="dev-mini-name">Klarenze Lonosa</div>
              <div class="dev-mini-role">Technical Documentation Specialist</div>
            </div>
          </button>
        </div>
      </aside>

      <article class="developer-detail reveal reveal-delay-2" id="developerSpotlight">
        <div class="developer-detail-layout">
          <div class="developer-visual-column">
            <div class="dev-spotlight-portrait" id="developerSpotlightPortrait">
              <img src="" alt="" class="dev-spotlight-photo" id="developerSpotlightPhoto">
              <span id="developerSpotlightInitials">RL</span>
            </div>
          </div>

          <div class="developer-copy-column">
            <div>
              <div class="dev-detail-top">
                <div>
                  <h3 class="dev-detail-title" id="developerDetailName">Renwel Lucero</h3>
                  <div class="dev-detail-role" id="developerDetailRole">Lead Developer</div>
                </div>
              </div>

              <p class="dev-detail-text" id="developerDetailDescription">Renwel Lucero leads the development direction of TereLearn, coordinating the overall implementation and ensuring the platform evolves into a cohesive learning management experience.</p>

              <div class="dev-skills" id="developerSkills">
                <span class="dev-skill-chip">Project Leadership</span>
                <span class="dev-skill-chip">System Implementation</span>
                <span class="dev-skill-chip">Platform Architecture</span>
              </div>
            </div>

            <div class="dev-detail-footer">
              <div class="dev-stat-card">
                <div class="dev-stat-label">Primary Focus</div>
                <div class="dev-stat-value" id="developerPrimaryFocus">Guides overall system direction and major implementation decisions.</div>
              </div>
              <div class="dev-stat-card">
                <div class="dev-stat-label">Contribution Lens</div>
                <div class="dev-stat-value" id="developerContributionLens">Keeps the product vision, functionality, and delivery aligned.</div>
              </div>
            </div>
          </div>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="lp-cta">
  <h2 class="cta-title reveal">Ready to start<br><em>learning smarter?</em></h2>
  <p class="cta-sub reveal reveal-delay-1">Sign in to your TereLearn account and experience education that truly transcends.</p>
  <div class="cta-btns reveal reveal-delay-2">
    <a href="#signin" class="btn-cta-white">Sign In Now →</a>
  </div>
</section>

<!-- FOOTER -->
<footer class="lp-footer">
  <div class="footer-brand">TereLearn</div>
  <div class="footer-school">Colegio de Sta. Teresa de Avila · School of Information Technology</div>
  <div class="footer-copy">© 2026 CSTA. All rights reserved.</div>
</footer>


<!-- ═══════════════════════════════════════════════════
     SIGN-IN SECTION
═══════════════════════════════════════════════════ -->
<section id="signin">

  <!-- LEFT PANEL: Hero -->
  <div class="left-panel">
    <div class="hero-shape hero-shape-1"></div>
    <div class="hero-shape hero-shape-2"></div>
    <div class="hero-shape hero-shape-3"></div>
    <div class="hero-shape hero-shape-4"></div>

    <div class="brand-mark">
      <img src="public/assets/img/logo/terelearn-logo-darkmode.png" alt="TereLearn logo">
    </div>
    <div class="brand-name">TereLearn</div>
    <div class="brand-tag">Education that Transcends</div>
  </div>

  <!-- RIGHT PANEL: Form -->
  <div class="right-panel">
    <div class="form-container">

      <!-- Credentials Step -->
      <div id="credStep">
        <div class="welcome-text">Welcome back</div>
        <p class="sub-text">Sign in to continue your journey</p>

        <div class="alert-box" id="credAlert"></div>

        <div class="form-group">
          <label for="username">Username or Email</label>
          <div class="input-wrap">
            <input type="text" id="username" class="form-control" placeholder="Enter your username" autocomplete="off">
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrap">
            <input type="password" id="password" class="form-control" style="padding-right:48px;" placeholder="Enter your password" autocomplete="new-password">
            <button class="eye-toggle" id="eyeBtn" type="button" onclick="toggleEye()" title="Show/hide password">
              <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path class="eye-path-show" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle class="eye-path-show" cx="12" cy="12" r="3"/>
                <path class="eye-path-hide" style="display:none" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line class="eye-path-hide" style="display:none" x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
        </div>

        <button class="btn-login" id="loginBtn" onclick="doLogin(event)">
          <div class="btn-inner"><span id="loginBtnText">Sign in</span></div>
        </button>

        <div class="form-footer">
          Forgot password? <a href="#" onclick="openForgot(event)">Reset it here</a>
        </div>
      </div>

      <!-- OTP Step -->
      <div id="otpStep" class="otp-step">
        <div class="welcome-text">Verify it's you</div>
        <p class="sub-text" id="otpSubtitle">Check your registered email for the code.</p>

        <div class="alert-box" id="otpAlert"></div>

        <div class="otp-info">
          A <strong>6-digit code</strong> was sent to <strong id="otpDestDisplay"></strong>.<br>
          The code expires in <strong>2 minutes</strong>.
        </div>

        <div class="form-group">
          <label>Verification Code</label>
          <input type="text" id="otpCode" class="form-control otp-input"
                 maxlength="6" placeholder="——————"
                 oninput="this.value=this.value.replace(/\D/g,'')">
        </div>

        <button class="btn-login" id="otpVerifyBtn" onclick="verifyOTP(event)">
          <div class="btn-inner"><span id="otpBtnText">Verify Code</span></div>
        </button>

        <div class="resend-row">
          <button class="resend-btn" id="resendEmailBtn" onclick="resendOTP('email')">Resend code</button>
          <span class="cooldown-timer" id="cooldownTimer"></span>
        </div>

        <button class="btn-ghost" onclick="backToLogin()">← Back to Sign In</button>
      </div>

    </div>
  </div>

</section>

<!-- Suspicious Login Modal -->
<div class="modal-overlay" id="suspiciousModal">
  <div class="modal-box">
    <div style="font-size:2.4rem;text-align:center;margin-bottom:16px;">⚠️</div>
    <h3>Suspicious Login Detected</h3>
    <p class="desc">
      Someone is repeatedly attempting to log in using your credentials.<br><br>
      <strong id="suspiciousDetail"></strong><br><br>
      If this wasn't you, consider changing your password immediately.
    </p>
    <div class="modal-btns">
      <button class="modal-btn secondary" onclick="closeSuspicious()">Dismiss</button>
      <button class="modal-btn primary" onclick="openForgot(null, true)">Change Password</button>
    </div>
  </div>
</div>

<!-- Role Selection Modal -->
<div class="modal-overlay" id="roleModal">
  <div class="modal-box">
    <h3>Select Your Role</h3>
    <p class="desc">Your account has dual access. How would you like to proceed today?</p>
    <div class="modal-btns" style="flex-direction:column;">
      <button class="modal-btn primary" onclick="selectRole('faculty')">👨‍🏫 Continue as Professor</button>
      <button class="modal-btn primary" onclick="selectRole('admin')">🏛️ Continue as Dean / Sub-Admin</button>
    </div>
  </div>
</div>

<!-- Set Password Modal -->
<div class="modal-overlay" id="pwdModal">
  <div class="modal-box">
    <h3>🔐 Set Your Password</h3>
    <p class="desc">For your security, please create a strong password before continuing.</p>
    <div class="alert-box" id="pwdAlert"></div>

    <div class="form-group">
      <label>New Password</label>
      <div class="input-wrap">
        <input type="password" id="newPwd" class="form-control" style="padding-right:48px;" placeholder="Enter a strong password" autocomplete="new-password">
        <button class="eye-toggle" type="button" onclick="toggleEye('newPwd',this)">
          <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <div class="strength-bar"><div class="strength-fill" id="sBar"></div></div>
      <div class="strength-label" id="sLabel">Strength: —</div>
      <div class="req-list">
        <div class="req-list-title">Requirements:</div>
        <div class="req-item" id="r-len"><div class="req-check">✓</div>At least 12 characters</div>
        <div class="req-item" id="r-up"><div class="req-check">✓</div>One uppercase letter (A-Z)</div>
        <div class="req-item" id="r-lo"><div class="req-check">✓</div>One lowercase letter (a-z)</div>
        <div class="req-item" id="r-num"><div class="req-check">✓</div>One number (0-9)</div>
        <div class="req-item" id="r-sp"><div class="req-check">✓</div>One special character (!@#$%^&*)</div>
      </div>
    </div>

    <div class="form-group" id="confirmGroup" style="display:none; margin-top:20px;">
      <label>Confirm Password</label>
      <div class="input-wrap">
        <input type="password" id="confirmPwd" class="form-control" style="padding-right:48px;" placeholder="Re-enter your password" autocomplete="new-password">
        <button class="eye-toggle" type="button" onclick="toggleEye('confirmPwd',this)">
          <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <div id="matchMsg" style="font-size:.78rem;margin-top:6px;font-weight:600;color:#cbd5e0;"></div>
    </div>

    <button class="btn-login" id="savePwdBtn" disabled onclick="saveNewPassword(event)">
      <div class="btn-inner"><span id="savePwdText">Update Password</span></div>
    </button>
  </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal-overlay" id="forgotModal">
  <div class="modal-box forgot-modal-box">
    <div class="forgot-modal-grid">
      <aside class="forgot-aside">
        <div>
          <div class="forgot-kicker">Recovery Flow</div>
          <h3>Reset your password without losing momentum.</h3>
          <p class="forgot-aside-copy">We’ll analyze the account you enter, send a recovery code to the email on file, verify the code, and let you set a new password in one guided flow.</p>

          <div class="forgot-steps">
            <div class="forgot-step-pill active" id="forgotStepPillLookup">
              <div class="forgot-step-number">1</div>
              <div>
                <div class="forgot-step-title">Find your account</div>
                <div class="forgot-step-desc">Enter the username or email tied to your account.</div>
              </div>
            </div>
            <div class="forgot-step-pill" id="forgotStepPillCode">
              <div class="forgot-step-number">2</div>
              <div>
                <div class="forgot-step-title">Verify recovery code</div>
                <div class="forgot-step-desc">Use the 6-digit code sent to your registered email.</div>
              </div>
            </div>
            <div class="forgot-step-pill" id="forgotStepPillReset">
              <div class="forgot-step-number">3</div>
              <div>
                <div class="forgot-step-title">Create a new password</div>
                <div class="forgot-step-desc">Set a strong password to regain access safely.</div>
              </div>
            </div>
          </div>
        </div>

        <div class="forgot-side-note">
          Your recovery session will expire after too many incorrect code attempts, so only continue when you have access to the email linked to your account.
        </div>
      </aside>

      <div class="forgot-main">
        <div class="alert-box" id="forgotAlert"></div>

        <section class="forgot-stage active" id="forgotStageLookup">
          <div class="forgot-stage-title">Account recovery</div>
          <p class="forgot-stage-desc">Enter your username or email first. We’ll check whether the account is valid before sending a code, so the process feels intentional instead of spammable.</p>

          <div class="form-group">
            <label for="forgotInput">Username or Email</label>
            <input type="text" id="forgotInput" class="form-control" placeholder="Enter username or email">
          </div>

          <div class="forgot-helper">
            We’ll send a recovery code only after the account is successfully matched and analyzed.
          </div>

          <div class="modal-btns" style="margin-top:24px;">
            <button class="modal-btn secondary" type="button" onclick="closeForgot()">Cancel</button>
            <button class="modal-btn primary" type="button" id="forgotBtn" onclick="sendRecovery()">Analyze & Send Code</button>
          </div>
        </section>

        <section class="forgot-stage" id="forgotStageVerify">
          <div class="forgot-stage-title">Verify your code</div>
          <p class="forgot-stage-desc">We sent a 6-digit recovery code to the email on file. Enter it below to continue to password reset.</p>

          <div class="forgot-status-card">
            <div class="forgot-status-icon">✉</div>
            <div>
              <div class="forgot-status-title">Recovery code sent</div>
              <div class="forgot-status-text">Code destination: <strong id="forgotDestination">your email</strong></div>
            </div>
          </div>

          <div class="form-group">
            <label for="forgotCodeInput">Recovery Code</label>
            <input type="text" id="forgotCodeInput" class="form-control forgot-otp-input" maxlength="6" placeholder="------" oninput="this.value=this.value.replace(/\D/g,'')">
          </div>

          <div class="forgot-code-row">
            <span>Wrong too many times and this recovery session will expire.</span>
            <button class="forgot-inline-btn" type="button" id="forgotResendBtn" onclick="resendRecoveryCode()">Resend code</button>
          </div>

          <div class="modal-btns">
            <button class="modal-btn secondary" type="button" onclick="goToForgotStage('lookup')">Back</button>
            <button class="modal-btn primary" type="button" id="forgotVerifyBtn" onclick="verifyRecoveryCode()">Verify Code</button>
          </div>
        </section>

        <section class="forgot-stage" id="forgotStageReset">
          <div class="forgot-stage-title">Set a new password</div>
          <p class="forgot-stage-desc">Your recovery code is verified. Create a strong new password for the account before signing back in.</p>

          <div class="forgot-password-grid">
            <div class="form-group">
              <label for="forgotNewPwd">New Password</label>
              <div class="input-wrap">
                <input type="password" id="forgotNewPwd" class="form-control" style="padding-right:48px;" placeholder="Enter a strong password" autocomplete="new-password">
                <button class="eye-toggle" type="button" onclick="toggleEye('forgotNewPwd',this)">
                  <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div class="strength-bar"><div class="strength-fill" id="forgotSBar"></div></div>
              <div class="strength-label" id="forgotSLabel">Strength: —</div>
              <div class="req-list">
                <div class="req-list-title">Requirements:</div>
                <div class="req-item" id="forgot-r-len"><div class="req-check">✓</div>At least 12 characters</div>
                <div class="req-item" id="forgot-r-up"><div class="req-check">✓</div>One uppercase letter (A-Z)</div>
                <div class="req-item" id="forgot-r-lo"><div class="req-check">✓</div>One lowercase letter (a-z)</div>
                <div class="req-item" id="forgot-r-num"><div class="req-check">✓</div>One number (0-9)</div>
                <div class="req-item" id="forgot-r-sp"><div class="req-check">✓</div>One special character (!@#$%^&*)</div>
              </div>
            </div>

            <div class="form-group">
              <label for="forgotConfirmPwd">Confirm Password</label>
              <div class="input-wrap">
                <input type="password" id="forgotConfirmPwd" class="form-control" style="padding-right:48px;" placeholder="Re-enter your password" autocomplete="new-password">
                <button class="eye-toggle" type="button" onclick="toggleEye('forgotConfirmPwd',this)">
                  <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div id="forgotMatchMsg" style="font-size:.78rem;margin-top:6px;font-weight:600;color:#cbd5e0;"></div>
            </div>
          </div>

          <div class="modal-btns" style="margin-top:8px;">
            <button class="modal-btn secondary" type="button" onclick="goToForgotStage('verify')">Back</button>
            <button class="modal-btn primary" type="button" id="forgotSaveBtn" disabled onclick="saveRecoveredPassword()">Update Password</button>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>

<script>
/* ── NAV SCROLL ── */
window.addEventListener('scroll', () => {
  document.getElementById('lpNav').classList.toggle('scrolled', window.scrollY > 40);
});

/* ── SCROLL REVEAL ── */
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

/* ── DEVELOPERS SHOWCASE ── */
const developers = [
  {
    name: 'Renwel Lucero',
    role: 'Lead Developer',
    initials: 'RL',
    imageBase: 'public/assets/img/developers/renwel-lucero',
    note: 'Leading the direction of TereLearn and shaping the platform into a polished, working academic system.',
    description: 'Renwel Lucero leads the development direction of TereLearn, coordinating the overall implementation and ensuring the platform evolves into a cohesive learning management experience.',
    primaryFocus: 'Guides overall system direction and major implementation decisions.',
    contributionLens: 'Keeps the product vision, functionality, and delivery aligned.',
    skills: ['Project Leadership', 'System Implementation', 'Platform Architecture'],
    gradient: 'linear-gradient(135deg, #291304, #fadd7d)'
  },
  {
    name: 'Larry Salva',
    role: 'System Analyst',
    initials: 'LS',
    imageBase: 'public/assets/img/developers/larry-salva',
    note: 'Translating system needs into clear structure, logic, and process flow for the platform.',
    description: 'Larry Salva focuses on system analysis, helping define workflows, align requirements, and keep the platform grounded in practical academic and administrative needs.',
    primaryFocus: 'Analyzes workflows, system behavior, and feature requirements.',
    contributionLens: 'Connects user needs with the structure of the final system.',
    skills: ['Workflow Analysis', 'Requirement Mapping', 'Process Design'],
    gradient: 'linear-gradient(135deg, #291304, #facb8c)'
  },
  {
    name: 'Jhun Rachell Mondido',
    role: 'QA Tester',
    initials: 'JM',
    imageBase: 'public/assets/img/developers/jhun-rachell-mondido',
    note: 'Protecting system quality by checking behavior, reliability, and user-facing flow.',
    description: 'Jhun Rachell Mondido strengthens the quality of TereLearn by validating features, spotting defects, and helping make sure each release works as expected before it reaches users.',
    primaryFocus: 'Tests core features, catches issues, and verifies behavior.',
    contributionLens: 'Helps make the user experience more stable and dependable.',
    skills: ['Quality Assurance', 'Bug Validation', 'Test Coverage'],
    gradient: 'linear-gradient(135deg, #6c543c, #fbd990)'
  },
  {
    name: 'Klarenze Lonosa',
    role: 'Technical Documentation Specialist',
    initials: 'KL',
    imageBase: 'public/assets/img/developers/klarenze-lonosa',
    note: 'Turning technical work into organized documentation that supports both users and the team.',
    description: 'Klarenze Lonosa handles technical documentation, making sure system processes, features, and references are clearly documented for smoother collaboration, onboarding, and maintenance.',
    primaryFocus: 'Documents processes, features, and technical references clearly.',
    contributionLens: 'Improves clarity, continuity, and knowledge transfer across the project.',
    skills: ['Technical Writing', 'Documentation Structure', 'Knowledge Transfer'],
    gradient: 'linear-gradient(135deg, #6c543c, #facb8c)'
  }
];

const developerSpotlight = document.getElementById('developerSpotlight');
const developerSpotlightPortrait = document.getElementById('developerSpotlightPortrait');
const developerSpotlightPhoto = document.getElementById('developerSpotlightPhoto');
const developerSpotlightInitials = document.getElementById('developerSpotlightInitials');
const developerDetailName = document.getElementById('developerDetailName');
const developerDetailRole = document.getElementById('developerDetailRole');
const developerDetailDescription = document.getElementById('developerDetailDescription');
const developerPrimaryFocus = document.getElementById('developerPrimaryFocus');
const developerContributionLens = document.getElementById('developerContributionLens');
const developerSkills = document.getElementById('developerSkills');
const developersTrack = document.getElementById('developersTrack');
const developerCardButtons = Array.from(document.querySelectorAll('.developer-mini-card'));
const developerImageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

let activeDeveloperIndex = 0;
let developerAutoSlide = null;
let developerSwitchTimeout = null;
let developerImageAttemptIndex = 0;
let currentDeveloperImageBase = '';

function loadMiniCardPhoto(button, developer) {
  const miniPhoto = button.querySelector('.dev-mini-photo');
  if (!miniPhoto || !developer.imageBase) return;

  const tryExtension = (extensionIndex) => {
    miniPhoto.src = `${developer.imageBase}.${developerImageExtensions[extensionIndex]}`;
  };

  miniPhoto.alt = `${developer.name} thumbnail`;
  button.classList.add('has-photo');
  tryExtension(0);

  miniPhoto.onerror = () => {
    const currentIndex = developerImageExtensions.findIndex(ext => miniPhoto.src.endsWith(`.${ext}`));
    const nextIndex = currentIndex + 1;

    if (nextIndex > 0 && nextIndex < developerImageExtensions.length) {
      tryExtension(nextIndex);
      return;
    }

    miniPhoto.removeAttribute('src');
    miniPhoto.alt = '';
    button.classList.remove('has-photo');
    miniPhoto.onerror = null;
  };
}

function applyDeveloperContent(developer) {
  developerSpotlightPortrait.style.background = developer.gradient;
  developerSpotlightInitials.textContent = developer.initials;

  if (developer.imageBase) {
    developerSpotlightPortrait.classList.add('has-photo');
    currentDeveloperImageBase = developer.imageBase;
    developerImageAttemptIndex = 0;
    developerSpotlightPhoto.src = `${developer.imageBase}.${developerImageExtensions[developerImageAttemptIndex]}`;
    developerSpotlightPhoto.alt = `${developer.name} portrait`;
    developerSpotlightInitials.style.opacity = '0';
  } else {
    developerSpotlightPortrait.classList.remove('has-photo');
    developerSpotlightPhoto.removeAttribute('src');
    developerSpotlightPhoto.alt = '';
    developerSpotlightInitials.style.opacity = '1';
    currentDeveloperImageBase = '';
    developerImageAttemptIndex = 0;
  }

  developerDetailName.textContent = developer.name;
  developerDetailRole.textContent = developer.role;
  developerDetailDescription.textContent = developer.description;
  developerPrimaryFocus.textContent = developer.primaryFocus;
  developerContributionLens.textContent = developer.contributionLens;
  developerSkills.innerHTML = developer.skills.map(skill => `<span class="dev-skill-chip">${skill}</span>`).join('');
}

if (developerSpotlightPhoto) {
  developerSpotlightPhoto.addEventListener('error', () => {
    if (currentDeveloperImageBase && developerImageAttemptIndex < developerImageExtensions.length - 1) {
      developerImageAttemptIndex += 1;
      developerSpotlightPhoto.src = `${currentDeveloperImageBase}.${developerImageExtensions[developerImageAttemptIndex]}`;
      return;
    }

    developerSpotlightPortrait.classList.remove('has-photo');
    developerSpotlightPhoto.removeAttribute('src');
    developerSpotlightPhoto.alt = '';
    developerSpotlightInitials.style.opacity = '1';
    currentDeveloperImageBase = '';
    developerImageAttemptIndex = 0;
  });
}

developerCardButtons.forEach((button, index) => {
  loadMiniCardPhoto(button, developers[index]);
});

function renderDeveloper(index, animateSpotlight = false) {
  const developer = developers[index];
  if (!developer) return;

  activeDeveloperIndex = index;

  if (developerSwitchTimeout) {
    clearTimeout(developerSwitchTimeout);
    developerSwitchTimeout = null;
  }

  if (animateSpotlight && developerSpotlight) {
    developerSpotlight.classList.remove('is-animating');
    void developerSpotlight.offsetWidth;
    developerSpotlight.classList.add('is-animating');
    developerSpotlight.classList.add('is-switching');

    developerSwitchTimeout = setTimeout(() => {
      applyDeveloperContent(developer);
      developerSpotlight.classList.remove('is-switching');
      developerSwitchTimeout = null;
    }, 220);

    setTimeout(() => developerSpotlight.classList.remove('is-animating'), 560);
  } else {
    applyDeveloperContent(developer);
  }

  developerCardButtons.forEach((button, buttonIndex) => {
    button.classList.toggle('active', buttonIndex === index);
  });

  const activeButton = developerCardButtons[index];
  if (activeButton && developersTrack) {
    const isMobileViewport = window.innerWidth <= 768;

    if (isMobileViewport) {
      const targetLeft = activeButton.offsetLeft - ((developersTrack.clientWidth - activeButton.offsetWidth) / 2);
      const maxScrollLeft = developersTrack.scrollWidth - developersTrack.clientWidth;
      const nextScrollLeft = Math.max(0, Math.min(targetLeft, maxScrollLeft));

      if (Math.abs(developersTrack.scrollLeft - nextScrollLeft) > 4) {
        developersTrack.scrollTo({ left: nextScrollLeft, behavior: 'smooth' });
      }
    } else {
      const targetTop = activeButton.offsetTop - ((developersTrack.clientHeight - activeButton.offsetHeight) / 2);
      const maxScrollTop = developersTrack.scrollHeight - developersTrack.clientHeight;
      const nextScrollTop = Math.max(0, Math.min(targetTop, maxScrollTop));

      if (Math.abs(developersTrack.scrollTop - nextScrollTop) > 4) {
        developersTrack.scrollTo({ top: nextScrollTop, behavior: 'smooth' });
      }
    }
  }
}

function startDeveloperAutoSlide() {
  clearInterval(developerAutoSlide);
  developerAutoSlide = setInterval(() => {
    const nextIndex = (activeDeveloperIndex + 1) % developers.length;
    renderDeveloper(nextIndex, true);
  }, 4500);
}

developerCardButtons.forEach(button => {
  button.addEventListener('click', () => {
    renderDeveloper(Number(button.dataset.devIndex), true);
    startDeveloperAutoSlide();
  });
});

if (developersTrack) {
  developersTrack.addEventListener('mouseenter', () => clearInterval(developerAutoSlide));
  developersTrack.addEventListener('mouseleave', startDeveloperAutoSlide);
  developersTrack.addEventListener('focusin', () => clearInterval(developerAutoSlide));
  developersTrack.addEventListener('focusout', startDeveloperAutoSlide);
}

renderDeveloper(0);
startDeveloperAutoSlide();

const developersSection = document.getElementById('developers');
const developersSidebar = document.querySelector('.developers-sidebar');
const developersNextSection = developersSection ? developersSection.nextElementSibling : null;

if ('scrollRestoration' in history) {
  history.scrollRestoration = 'manual';
}

window.addEventListener('load', () => {
  if (window.location.hash) {
    history.replaceState(null, '', window.location.pathname + window.location.search);
  }
  window.scrollTo(0, 0);
});

function updateMobileDevelopersFloat() {
  if (!developersSection || !developersSidebar) return;

  if (window.innerWidth > 768) {
    developersSidebar.classList.remove('mobile-floating');
    return;
  }

  const sectionRect = developersSection.getBoundingClientRect();
  const viewportHeight = window.innerHeight;
  const sidebarHeight = developersSidebar.offsetHeight;
  const navHeight = 72;
  const isInsideDevelopers = sectionRect.top <= viewportHeight - navHeight && sectionRect.bottom > navHeight;
  const nextSectionTop = developersNextSection ? developersNextSection.getBoundingClientRect().top : Number.POSITIVE_INFINITY;
  const shouldDockBack = nextSectionTop <= viewportHeight - (sidebarHeight + 20);

  if (isInsideDevelopers && !shouldDockBack) {
    developersSidebar.classList.add('mobile-floating');
  } else {
    developersSidebar.classList.remove('mobile-floating');
  }
}

updateMobileDevelopersFloat();
window.addEventListener('scroll', updateMobileDevelopersFloat, { passive: true });
window.addEventListener('resize', updateMobileDevelopersFloat);

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', event => {
    const targetId = anchor.getAttribute('href');
    if (!targetId || targetId === '#') return;

    const target = document.querySelector(targetId);
    if (!target) return;

    event.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });

    window.setTimeout(() => {
      history.replaceState(null, '', window.location.pathname + window.location.search);
    }, 700);
  });
});

/* ── RIPPLE ── */
function addRipple(btn, e) {
  const ripple = document.createElement('span');
  ripple.className = 'ripple';
  const rect = btn.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height) * 2;
  ripple.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - rect.left - size/2}px;top:${e.clientY - rect.top - size/2}px;`;
  btn.appendChild(ripple);
  setTimeout(() => ripple.remove(), 600);
}

['loginBtn','otpVerifyBtn','savePwdBtn'].forEach(id => {
  const btn = document.getElementById(id);
  if (btn) btn.addEventListener('mousedown', e => addRipple(btn, e));
});

/* ── STATE ── */
let currentUser = null;
let isFirstLogin = false;
let otpChannel = 'email';
let resendTimer = null;
let cooldownLeft = 0;
let failedAttempts = 0;
const MAX_FAILS = 5;
let eyeVisible = false;

/* ── HELPERS ── */
function showAlert(id, msg, type) {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.className = 'alert-box ' + type;
  el.style.display = 'block';
}

function hideAlert(id) {
  document.getElementById(id).style.display = 'none';
}

function setBtn(id, loading, label) {
  const b = document.getElementById(id);
  b.disabled = loading;
  const txt = b.querySelector('[id$="Text"]') || b.querySelector('span');
  if (txt) txt.innerHTML = loading ? `${label} <span class="spinner"></span>` : label;
}

const LOGIN_SUBMIT_MIN_DELAY_MS = 1500;
let loginRequestInFlight = false;
let loginCooldownUntil = 0;
const RECOVERY_MAX_VERIFY_ATTEMPTS = 5;
let recoveryFlow = {
  step: 'lookup',
  userId: '',
  lookup: '',
  destination: '',
  verified: false,
  verifyAttempts: 0
};

function getLoginCooldownRemaining() {
  return Math.max(0, loginCooldownUntil - Date.now());
}

function waitForLoginCooldown() {
  const remaining = getLoginCooldownRemaining();
  if (remaining <= 0) return Promise.resolve();
  return new Promise(resolve => setTimeout(resolve, remaining));
}

function resetForgotFlow() {
  recoveryFlow = {
    step: 'lookup',
    userId: '',
    lookup: '',
    destination: '',
    verified: false,
    verifyAttempts: 0
  };

  hideAlert('forgotAlert');
  const forgotInput = document.getElementById('forgotInput');
  const forgotCodeInput = document.getElementById('forgotCodeInput');
  const forgotNewPwd = document.getElementById('forgotNewPwd');
  const forgotConfirmPwd = document.getElementById('forgotConfirmPwd');

  if (forgotInput) forgotInput.value = '';
  if (forgotCodeInput) forgotCodeInput.value = '';
  if (forgotNewPwd) forgotNewPwd.value = '';
  if (forgotConfirmPwd) forgotConfirmPwd.value = '';

  document.getElementById('forgotDestination').textContent = 'your email';
  document.getElementById('forgotMatchMsg').textContent = '';
  document.getElementById('forgotSaveBtn').disabled = true;
  document.getElementById('forgotSBar').className = 'strength-fill';
  document.getElementById('forgotSLabel').className = 'strength-label';
  document.getElementById('forgotSLabel').textContent = 'Strength: —';

  ['forgot-r-len', 'forgot-r-up', 'forgot-r-lo', 'forgot-r-num', 'forgot-r-sp'].forEach(id => {
    document.getElementById(id).classList.remove('met');
  });

  setBtn('forgotBtn', false, 'Analyze & Send Code');
  setBtn('forgotVerifyBtn', false, 'Verify Code');
  setBtn('forgotSaveBtn', false, 'Update Password');
  document.getElementById('forgotResendBtn').disabled = false;
  goToForgotStage('lookup');
}

function closeForgot() {
  document.getElementById('forgotModal').classList.remove('active');
  resetForgotFlow();
}

function goToForgotStage(step) {
  recoveryFlow.step = step;
  const stages = {
    lookup: 'forgotStageLookup',
    verify: 'forgotStageVerify',
    reset: 'forgotStageReset'
  };
  Object.entries(stages).forEach(([key, id]) => {
    document.getElementById(id).classList.toggle('active', key === step);
  });

  const pillState = {
    lookup: ['active', '', ''],
    verify: ['done', 'active', ''],
    reset: ['done', 'done', 'active']
  }[step];

  ['forgotStepPillLookup', 'forgotStepPillCode', 'forgotStepPillReset'].forEach((id, index) => {
    const el = document.getElementById(id);
    el.classList.remove('active', 'done');
    if (pillState[index]) el.classList.add(pillState[index]);
  });
}

function initForgotStrength() {
  const pwd = document.getElementById('forgotNewPwd');
  const confirm = document.getElementById('forgotConfirmPwd');

  pwd.oninput = () => {
    const value = pwd.value;
    const checks = Object.keys(POLICY).map(k => POLICY[k](value));
    const allMet = checks.every(Boolean);
    const met = checks.filter(Boolean).length;

    document.getElementById('forgot-r-len').classList.toggle('met', POLICY.length(value));
    document.getElementById('forgot-r-up').classList.toggle('met', POLICY.uppercase(value));
    document.getElementById('forgot-r-lo').classList.toggle('met', POLICY.lowercase(value));
    document.getElementById('forgot-r-num').classList.toggle('met', POLICY.number(value));
    document.getElementById('forgot-r-sp').classList.toggle('met', POLICY.special(value));

    let lvl = '';
    let lbl = 'Strength: —';
    if (!value.length) { lvl = ''; lbl = 'Strength: —'; }
    else if (allMet) { lvl = 'excellent'; lbl = 'Strength: Excellent ✓'; }
    else if (met >= 4) { lvl = 'strong'; lbl = 'Strength: Strong'; }
    else if (met >= 3) { lvl = 'good'; lbl = 'Strength: Good'; }
    else if (met >= 2) { lvl = 'fair'; lbl = 'Strength: Fair'; }
    else { lvl = 'weak'; lbl = 'Strength: Weak'; }

    document.getElementById('forgotSBar').className = 'strength-fill ' + lvl;
    document.getElementById('forgotSLabel').className = 'strength-label ' + lvl;
    document.getElementById('forgotSLabel').textContent = lbl;

    if (!allMet) {
      confirm.value = '';
      document.getElementById('forgotMatchMsg').textContent = '';
    }

    evalForgotSave();
  };

  confirm.oninput = evalForgotSave;
}

function evalForgotSave() {
  const p = document.getElementById('forgotNewPwd').value;
  const c = document.getElementById('forgotConfirmPwd').value;
  const ok = Object.keys(POLICY).every(k => POLICY[k](p));
  const match = p === c && c !== '';
  const mm = document.getElementById('forgotMatchMsg');
  mm.textContent = c === '' ? '' : (match ? '✓ Passwords match' : '✗ Passwords do not match');
  mm.style.color = match ? '#6c543c' : '#ef4444';
  document.getElementById('forgotSaveBtn').disabled = !(ok && match && recoveryFlow.verified);
}

/* ── EYE TOGGLE ── */
function toggleEye(inputId, btn) {
  if (!inputId) {
    eyeVisible = !eyeVisible;
    const inp = document.getElementById('password');
    inp.type = eyeVisible ? 'text' : 'password';
    const btnEl = document.getElementById('eyeBtn');
    btnEl.classList.toggle('active', eyeVisible);
    document.querySelectorAll('.eye-path-show').forEach(el => el.style.display = eyeVisible ? 'none' : '');
    document.querySelectorAll('.eye-path-hide').forEach(el => el.style.display = eyeVisible ? '' : 'none');
    return;
  }
  const inp = document.getElementById(inputId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  const isVisible = inp.type === 'text';
  btn.classList.toggle('active', isVisible);
  const ic = btn.querySelector('svg');
  ic.querySelectorAll('.eye-path-show').forEach(el => el.style.display = isVisible ? 'none' : '');
  ic.querySelectorAll('.eye-path-hide').forEach(el => el.style.display = isVisible ? '' : 'none');
}

/* ── ENTER KEY ── */
document.addEventListener('keydown', e => {
  if (e.key !== 'Enter') return;
  if (document.getElementById('credStep').style.display !== 'none') {
    document.getElementById('loginBtn').click();
  } else if (document.getElementById('otpStep').style.display !== 'none') {
    document.getElementById('otpVerifyBtn').click();
  }
});

/* ── LOGIN ── */
async function doLogin(e) {
  if (loginRequestInFlight) return;

  const cooldownRemaining = getLoginCooldownRemaining();
  if (cooldownRemaining > 0) {
    showAlert('credAlert', 'Please wait a moment before trying to sign in again.', 'warning');
    return;
  }

  if (e) addRipple(document.getElementById('loginBtn'), e);
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  if (!username || !password) { showAlert('credAlert', 'Please fill in all fields.', 'error'); return; }

  hideAlert('credAlert');
  loginRequestInFlight = true;
  loginCooldownUntil = Date.now() + LOGIN_SUBMIT_MIN_DELAY_MS;
  setBtn('loginBtn', true, 'Signing in');

  try {
    const res = await fetch('API/authenticate.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    });
    const data = await res.json();

    if (data.success) {
      failedAttempts = 0;
      currentUser = data.user;
      isFirstLogin = data.first_login === 1;
      await waitForLoginCooldown();
      setBtn('loginBtn', false, 'Sign in');
      if (isFirstLogin) {
        await triggerOTP('email');
      } else if (data.otp_required === false) {
        proceedAfterOTP();
      } else {
        await triggerOTP('email');
      }
    } else {
      failedAttempts++;
      await waitForLoginCooldown();
      setBtn('loginBtn', false, 'Sign in');
      showAlert('credAlert', data.message || 'Invalid username or password.', 'error');
      if (failedAttempts >= MAX_FAILS && data.owner_email) {
        await notifySuspicious(username, data.owner_email, data.owner_phone);
      }
    }
  } catch (err) {
    await waitForLoginCooldown();
    setBtn('loginBtn', false, 'Sign in');
    showAlert('credAlert', 'Connection error. Please try again.', 'error');
  } finally {
    loginRequestInFlight = false;
  }
}

/* ── OTP ── */
async function triggerOTP(channel) {
  otpChannel = channel;
  try {
    const res = await fetch('API/send_otp.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: currentUser.id, channel, is_initial: true })
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('otpDestDisplay').textContent = data.destination;
      document.getElementById('otpSubtitle').textContent =
        channel === 'email' ? 'Check your registered email for the code.' : 'Check your phone — we sent you an SMS.';
      document.getElementById('credStep').style.display = 'none';
      document.getElementById('otpStep').style.display = 'block';
      hideAlert('otpAlert');
      document.getElementById('otpCode').value = '';
      startCooldown(60);
    } else {
      document.getElementById('credStep').style.display = 'none';
      document.getElementById('otpStep').style.display = 'block';
      showAlert('otpAlert', data.message || 'Could not send OTP. Try resending.', 'warning');
    }
  } catch (err) {
    showAlert('credAlert', 'Failed to send verification code. Please retry.', 'error');
  }
}

async function resendOTP(channel) {
  if (cooldownLeft > 0) return;
  hideAlert('otpAlert');
  const btn = document.getElementById('resendEmailBtn');
  btn.disabled = true;
  try {
    const res = await fetch('API/send_otp.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: currentUser.id, channel })
    });
    const data = await res.json();
    if (data.success) {
      otpChannel = channel;
      document.getElementById('otpDestDisplay').textContent = data.destination;
      document.getElementById('otpSubtitle').textContent =
        channel === 'email' ? 'Check your registered email for the code.' : 'Check your phone — we sent you an SMS.';
      showAlert('otpAlert', 'Code re-sent successfully.', 'success');
      startCooldown(60);
    } else {
      showAlert('otpAlert', data.message || 'Resend failed.', 'error');
    }
  } catch (err) {
    showAlert('otpAlert', 'Resend failed. Try again.', 'error');
  } finally {
    btn.disabled = false;
  }
}

function startCooldown(seconds) {
  cooldownLeft = seconds;
  const resendBtn = document.getElementById('resendEmailBtn');
  const timer = document.getElementById('cooldownTimer');
  resendBtn.disabled = true;
  clearInterval(resendTimer);
  resendTimer = setInterval(() => {
    cooldownLeft--;
    timer.textContent = cooldownLeft > 0 ? `Wait ${cooldownLeft}s` : '';
    if (cooldownLeft <= 0) {
      clearInterval(resendTimer);
      resendBtn.disabled = false;
    }
  }, 1000);
}

async function verifyOTP(e) {
  const code = document.getElementById('otpCode').value.trim();
  if (code.length !== 6) { showAlert('otpAlert', 'Enter the 6-digit code.', 'error'); return; }
  hideAlert('otpAlert');
  setBtn('otpVerifyBtn', true, 'Verifying');
  try {
    const res = await fetch('API/verify_otp.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: currentUser.id, code })
    });
    const data = await res.json();
    if (data.success) {
      setBtn('otpVerifyBtn', false, 'Verify Code');
      clearInterval(resendTimer);
      proceedAfterOTP();
    } else {
      setBtn('otpVerifyBtn', false, 'Verify Code');
      showAlert('otpAlert', data.message || 'Invalid or expired code.', 'error');
    }
  } catch (err) {
    setBtn('otpVerifyBtn', false, 'Verify Code');
    showAlert('otpAlert', 'Verification error. Try again.', 'error');
  }
}

function proceedAfterOTP() {
  document.getElementById('otpStep').style.display = 'none';
  if (isFirstLogin) {
    document.getElementById('pwdModal').classList.add('active');
    initStrength();
  } else {
    routeUser(currentUser);
  }
}

function routeUser(user) {
  const lvl = parseInt(user.user_level_id);
  const dean = parseInt(user.is_dean);
  if (lvl === 1) {
    showAlert('credAlert', '✓ Welcome, System Admin!', 'success');
    setTimeout(() => location.href = 'admin.php', 1400);
  } else if (lvl === 2) {
    if (dean === 1) {
      document.getElementById('roleModal').classList.add('active');
    } else {
      showAlert('credAlert', '✓ Welcome, Professor!', 'success');
      setTimeout(() => location.href = 'facultyUI.php', 1400);
    }
  } else if (lvl === 3) {
    showAlert('credAlert', '✓ Welcome!', 'success');
    setTimeout(() => location.href = 'student.php', 1400);
  } else if (lvl === 4 || lvl === 5) {
    const roleLabel = user.role ? ucFirst(user.role) : 'Dean';
    showAlert('credAlert', `✓ Welcome, ${roleLabel}!`, 'success');
    setTimeout(() => location.href = 'subadmin.php', 1400);
  } else {
    showAlert('credAlert', 'Unknown role. Contact your administrator.', 'error');
  }
}

function ucFirst(str) { return str ? str.charAt(0).toUpperCase() + str.slice(1) : str; }

function selectRole(role) {
  document.getElementById('roleModal').classList.remove('active');
  if (role === 'faculty') {
    showAlert('credAlert', '✓ Welcome, Professor!', 'success');
    setTimeout(() => location.href = 'facultyUI.php', 1400);
  } else {
    showAlert('credAlert', '✓ Welcome, Dean!', 'success');
    setTimeout(() => location.href = 'subadmin.php', 1400);
  }
}

function backToLogin() {
  document.getElementById('otpStep').style.display = 'none';
  document.getElementById('credStep').style.display = 'block';
  document.getElementById('otpCode').value = '';
  clearInterval(resendTimer);
  hideAlert('otpAlert');
}

/* ── SUSPICIOUS ── */
async function notifySuspicious(attemptedUser, ownerEmail, ownerPhone) {
  try {
    await fetch('API/notify_suspicious.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: attemptedUser, owner_email: ownerEmail, owner_phone: ownerPhone })
    });
  } catch (err) {}
  document.getElementById('suspiciousDetail').textContent =
    `Account: ${attemptedUser} — ${MAX_FAILS} failed attempts detected. A notification has been sent to the account owner.`;
  document.getElementById('suspiciousModal').classList.add('active');
}

function closeSuspicious() {
  document.getElementById('suspiciousModal').classList.remove('active');
  failedAttempts = 0;
}

/* ── PASSWORD STRENGTH ── */
const POLICY = {
  length: p => p.length >= 12,
  uppercase: p => /[A-Z]/.test(p),
  lowercase: p => /[a-z]/.test(p),
  number: p => /[0-9]/.test(p),
  special: p => /[!@#$%^&*()_+\-=[\]{};':'\\|,.<>\/?]/.test(p)
};

function initStrength() {
  const newPwd = document.getElementById('newPwd');
  const conPwd = document.getElementById('confirmPwd');
  const fresh = newPwd.cloneNode(true);
  newPwd.parentNode.replaceChild(fresh, newPwd);
  const freshC = conPwd.cloneNode(true);
  conPwd.parentNode.replaceChild(freshC, conPwd);

  fresh.addEventListener('input', () => {
    const p = fresh.value;
    const checks = Object.keys(POLICY).map(k => POLICY[k](p));
    const allMet = checks.every(Boolean);
    const met = checks.filter(Boolean).length;

    document.getElementById('r-len').classList.toggle('met', POLICY.length(p));
    document.getElementById('r-up').classList.toggle('met', POLICY.uppercase(p));
    document.getElementById('r-lo').classList.toggle('met', POLICY.lowercase(p));
    document.getElementById('r-num').classList.toggle('met', POLICY.number(p));
    document.getElementById('r-sp').classList.toggle('met', POLICY.special(p));

    let lvl = '', lbl = 'Strength: —';
    if (!p.length) { lvl = ''; lbl = 'Strength: —'; }
    else if (allMet) { lvl = 'excellent'; lbl = 'Strength: Excellent ✓'; }
    else if (met >= 4) { lvl = 'strong'; lbl = 'Strength: Strong'; }
    else if (met >= 3) { lvl = 'good'; lbl = 'Strength: Good'; }
    else if (met >= 2) { lvl = 'fair'; lbl = 'Strength: Fair'; }
    else { lvl = 'weak'; lbl = 'Strength: Weak'; }

    document.getElementById('sBar').className = 'strength-fill ' + lvl;
    document.getElementById('sLabel').className = 'strength-label ' + lvl;
    document.getElementById('sLabel').textContent = lbl;

    document.getElementById('confirmGroup').style.display = allMet ? 'block' : 'none';
    if (!allMet) freshC.value = '';
    evalSave();
  });
  freshC.addEventListener('input', evalSave);
}

function evalSave() {
  const p = document.getElementById('newPwd').value;
  const c = document.getElementById('confirmPwd').value;
  const ok = Object.keys(POLICY).every(k => POLICY[k](p));
  const match = p === c && c !== '';
  const mm = document.getElementById('matchMsg');
  mm.textContent = c === '' ? '' : (match ? '✓ Passwords match' : '✗ Passwords do not match');
  mm.style.color = match ? '#6c543c' : '#ef4444';
  document.getElementById('savePwdBtn').disabled = !(ok && match);
}

async function saveNewPassword(e) {
  const p = document.getElementById('newPwd').value;
  const c = document.getElementById('confirmPwd').value;
  if (!Object.keys(POLICY).every(k => POLICY[k](p))) { showAlert('pwdAlert', 'Password does not meet all requirements.', 'error'); return; }
  if (p !== c) { showAlert('pwdAlert', 'Passwords do not match.', 'error'); return; }

  setBtn('savePwdBtn', true, 'Saving');
  try {
    const res = await fetch('API/change_password.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: currentUser.id, new_password: p })
    });
    const data = await res.json();
    if (data.success) {
      showAlert('pwdAlert', '✓ Password updated! Redirecting…', 'success');
      setTimeout(() => {
        document.getElementById('pwdModal').classList.remove('active');
        routeUser(currentUser);
      }, 1400);
    } else {
      showAlert('pwdAlert', data.message || 'Update failed.', 'error');
      setBtn('savePwdBtn', false, 'Update Password');
    }
  } catch (err) {
    showAlert('pwdAlert', 'Connection error.', 'error');
    setBtn('savePwdBtn', false, 'Update Password');
  }
}

/* ── FORGOT ── */
function openForgot(e, fromSuspicious = false) {
  if (e) e.preventDefault();
  if (fromSuspicious) document.getElementById('suspiciousModal').classList.remove('active');
  resetForgotFlow();
  const existingUsername = document.getElementById('username').value.trim();
  if (existingUsername) document.getElementById('forgotInput').value = existingUsername;
  document.getElementById('forgotModal').classList.add('active');
}

async function sendRecovery() {
  const val = document.getElementById('forgotInput').value.trim();
  if (!val) { showAlert('forgotAlert', 'Enter your username or email.', 'error'); return; }
  hideAlert('forgotAlert');
  setBtn('forgotBtn', true, 'Analyzing');
  try {
    await new Promise(resolve => setTimeout(resolve, 700));
    const res = await fetch('API/send_otp.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ lookup: val, channel: 'recovery' })
    });
    const data = await res.json();
    if (data.success) {
      recoveryFlow.userId = data.user_id || '';
      recoveryFlow.lookup = val;
      recoveryFlow.destination = data.destination || 'your email';
      recoveryFlow.verified = false;
      recoveryFlow.verifyAttempts = 0;
      document.getElementById('forgotDestination').textContent = recoveryFlow.destination;
      document.getElementById('forgotCodeInput').value = '';
      showAlert('forgotAlert', `Recovery code sent to ${data.destination}.`, 'success');
      goToForgotStage('verify');
    } else {
      showAlert('forgotAlert', data.message || 'Account not found.', 'error');
    }
  } catch (err) {
    showAlert('forgotAlert', 'Error sending recovery code. Try again.', 'error');
  } finally {
    setBtn('forgotBtn', false, 'Analyze & Send Code');
  }
}

async function resendRecoveryCode() {
  if (!recoveryFlow.lookup) {
    showAlert('forgotAlert', 'Start the recovery flow again first.', 'warning');
    goToForgotStage('lookup');
    return;
  }

  document.getElementById('forgotResendBtn').disabled = true;
  document.getElementById('forgotInput').value = recoveryFlow.lookup;
  await sendRecovery();
  document.getElementById('forgotResendBtn').disabled = false;
}

async function verifyRecoveryCode() {
  const code = document.getElementById('forgotCodeInput').value.trim();
  if (!recoveryFlow.userId) {
    showAlert('forgotAlert', 'Recovery session missing. Please start again.', 'error');
    goToForgotStage('lookup');
    return;
  }

  if (code.length !== 6) {
    showAlert('forgotAlert', 'Enter the 6-digit recovery code.', 'error');
    return;
  }

  hideAlert('forgotAlert');
  setBtn('forgotVerifyBtn', true, 'Verifying');

  try {
    const res = await fetch('API/verify_otp.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: recoveryFlow.userId, code, context: 'recovery' })
    });
    const data = await res.json();

    if (data.success) {
      recoveryFlow.verified = true;
      showAlert('forgotAlert', 'Recovery code verified. You can now set a new password.', 'success');
      goToForgotStage('reset');
      initForgotStrength();
      evalForgotSave();
      return;
    }

    if (data.session_expired) {
      showAlert('credAlert', data.message || 'Recovery session expired. Please start again.', 'warning');
      closeForgot();
      return;
    }

    recoveryFlow.verifyAttempts += 1;
    const remainingAttempts = Number.isInteger(data.remaining_attempts)
      ? data.remaining_attempts
      : Math.max(0, RECOVERY_MAX_VERIFY_ATTEMPTS - recoveryFlow.verifyAttempts);

    showAlert(
      'forgotAlert',
      `${data.message || 'Incorrect code.'} ${remainingAttempts > 0 ? `${remainingAttempts} attempt(s) remaining.` : ''}`.trim(),
      'error'
    );
  } catch (err) {
    showAlert('forgotAlert', 'Could not verify the recovery code. Try again.', 'error');
  } finally {
    setBtn('forgotVerifyBtn', false, 'Verify Code');
  }
}

async function saveRecoveredPassword() {
  const p = document.getElementById('forgotNewPwd').value;
  const c = document.getElementById('forgotConfirmPwd').value;
  if (!recoveryFlow.userId || !recoveryFlow.verified) {
    showAlert('forgotAlert', 'Verify your recovery code first.', 'error');
    return;
  }
  if (!Object.keys(POLICY).every(k => POLICY[k](p))) { showAlert('forgotAlert', 'Password does not meet all requirements.', 'error'); return; }
  if (p !== c) { showAlert('forgotAlert', 'Passwords do not match.', 'error'); return; }

  setBtn('forgotSaveBtn', true, 'Saving');
  try {
    const res = await fetch('API/change_password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: recoveryFlow.userId, new_password: p })
    });
    const data = await res.json();
    if (data.success) {
      showAlert('forgotAlert', 'Password updated successfully. You can sign in now.', 'success');
      setTimeout(() => closeForgot(), 1400);
    } else {
      showAlert('forgotAlert', data.message || 'Password update failed.', 'error');
      setBtn('forgotSaveBtn', false, 'Update Password');
    }
  } catch (err) {
    showAlert('forgotAlert', 'Connection error while updating password.', 'error');
    setBtn('forgotSaveBtn', false, 'Update Password');
  }
}

/* ── CLEAR ON LOAD ── */
window.addEventListener('load', () => {
  document.getElementById('username').value = '';
  document.getElementById('password').value = '';
}, { once: true });
</script>

<script>
/* ── AUTO-REFRESH POLLING ENGINE ── */
let _pollsRestart = null;
const _polls = {};
function startPoll(key, fn, intervalMs = 20000) {
  stopPoll(key);
  fn(); // run immediately
  _polls[key] = setInterval(fn, intervalMs);
}
function stopPoll(key) {
  if (_polls[key]) { clearInterval(_polls[key]); delete _polls[key]; }
}
// Pause polling when tab is hidden, resume when visible
document.addEventListener('visibilitychange', () => {
  Object.values(_polls).forEach(id => {
    if (document.hidden) clearInterval(id);
  });
  if (!document.hidden) {
    // re-trigger registered polls
    Object.keys(_polls).forEach(key => _polls[key] && clearInterval(_polls[key]));
    _pollsRestart && _pollsRestart();
  }
});
</script>
</body>
</html>
