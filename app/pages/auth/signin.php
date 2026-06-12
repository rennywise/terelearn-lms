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
      --green-50: #f0fdf4;
      --green-100: #dcfce7;
      --green-200: #bbf7d0;
      --green-300: #86efac;
      --green-400: #4ade80;
      --green-500: #22c55e;
      --green-600: #16a34a;
      --green-700: #15803d;
      --green-800: #166534;
      --green-900: #14532d;
      --text-primary: #0f172a;
      --text-secondary: #64748b;
      --text-muted: #94a3b8;
      --border: #e2e8f0;
      --bg: #f8fafc;
      --white: #ffffff;
      --radius-sm: 8px;
      --radius: 16px;
      --radius-lg: 24px;
      --shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
      --shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.04);
      --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.04);
      --shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.15);
      --shadow-glow: 0 0 40px rgba(34,197,94,0.15);
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
      background: rgba(22, 101, 52, 0.92);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(255,255,255,0.08);
      transition: all 0.3s ease;
    }

    .lp-nav.scrolled {
      padding: 12px 48px;
      background: rgba(15, 74, 38, 0.97);
    }

    .lp-nav-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }

    .lp-nav-brand-icon {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .lp-nav-brand-icon svg {
      width: 18px;
      height: 18px;
      fill: none;
      stroke: white;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .lp-nav-brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.3rem;
      font-weight: 600;
      color: white;
      letter-spacing: -0.01em;
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
    .hero-orb-1 { width: 600px; height: 600px; background: #4ade80; top: -200px; right: -100px; animation: orbFloat 16s ease-in-out infinite; }
    .hero-orb-2 { width: 400px; height: 400px; background: #86efac; bottom: -100px; left: -100px; animation: orbFloat 20s ease-in-out infinite reverse; }
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
      background: linear-gradient(135deg, #4ade80, #22c55e);
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
      color: #86efac;
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
      background: #f0fdf4;
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

    .cluster-bar.high { height: 72px; background: linear-gradient(180deg, #4ade80, #16a34a); }
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

    .pill-dot.green { background: #4ade80; }
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
      background: linear-gradient(135deg, #dcfce7, #bbf7d0);
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
      box-shadow: 0 4px 12px rgba(22,163,74,0.3);
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
      box-shadow: 0 4px 16px rgba(22,163,74,0.08);
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

    .cta-title em { font-style: italic; color: #86efac; }

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
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: rgba(255,255,255,0.1);
      border: 1.5px solid rgba(255,255,255,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 28px;
      position: relative;
      z-index: 2;
      backdrop-filter: blur(12px);
      transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.4s;
      animation: gentlePulse 4s ease-in-out infinite;
    }

    .brand-mark:hover {
      transform: scale(1.08) translateY(-2px);
      box-shadow: 0 0 30px rgba(255,255,255,0.15);
    }

    @keyframes gentlePulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.12); }
      50% { box-shadow: 0 0 0 18px rgba(255,255,255,0); }
    }

    .brand-mark svg {
      width: 32px;
      height: 32px;
      fill: none;
      stroke: rgba(255,255,255,0.9);
      stroke-width: 1.5;
      stroke-linecap: round;
      stroke-linejoin: round;
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
      background: radial-gradient(circle, rgba(34,197,94,0.08) 0%, transparent 70%);
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
      background: radial-gradient(circle, rgba(132,204,22,0.06) 0%, transparent 70%);
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
      box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.08), 0 1px 3px rgba(0,0,0,0.05);
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
      background: linear-gradient(135deg, var(--green-600) 0%, var(--green-500) 50%, var(--green-400) 100%);
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
      box-shadow: 0 4px 14px rgba(34, 197, 94, 0.25);
    }

    .btn-login::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.15) 50%, transparent 100%);
      transform: translateX(-100%);
      transition: transform 0.6s ease;
    }

    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(34, 197, 94, 0.35); background-position: 100% 0; }
    .btn-login:hover::before { transform: translateX(100%); }
    .btn-login:active { transform: translateY(0) scale(0.98); box-shadow: 0 2px 8px rgba(34, 197, 94, 0.25); }
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
    .alert-box.success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

    .otp-step { display: none; }

    .otp-info {
      background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
      border: 1px solid #bbf7d0;
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

    .btn-ghost:hover { border-color: var(--green-400); color: var(--green-600); background: rgba(34, 197, 94, 0.03); }

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
    .modal-btn.primary { background: linear-gradient(135deg, var(--green-600), var(--green-500)); color: white; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2); }
    .modal-btn.primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(34, 197, 94, 0.3); }
    .modal-btn.secondary { background: var(--bg); color: var(--text-secondary); border: 1.5px solid var(--border); }
    .modal-btn.secondary:hover { border-color: var(--green-400); color: var(--green-600); background: rgba(34, 197, 94, 0.03); }

    .strength-bar { height: 4px; background: var(--border); border-radius: 2px; margin: 12px 0 8px; overflow: hidden; }
    .strength-fill { height: 100%; width: 0; border-radius: 2px; transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1); }
    .strength-fill.weak { width: 20%; background: #ef4444; }
    .strength-fill.fair { width: 40%; background: #f97316; }
    .strength-fill.good { width: 60%; background: #eab308; }
    .strength-fill.strong { width: 80%; background: #84cc16; }
    .strength-fill.excellent { width: 100%; background: #22c55e; }
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
    }

    @media (max-width: 768px) {
      .lp-nav { padding: 14px 24px; }
      .lp-nav-links { display: none; }
      .lp-hero { padding: 100px 24px 60px; }
      .lp-features, .lp-how, .lp-cta { padding: 72px 24px; }
      .features-grid { grid-template-columns: 1fr; }
      .lp-footer { padding: 32px 24px; flex-direction: column; text-align: center; }
      .hero-notif { right: 0; top: -16px; }
      .hero-student-img { display: none; }

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
      <svg viewBox="0 0 24 24">
        <path d="M22 10v6M2 10l10-5 10 5z"/>
        <path d="M6 12v5c0 1.5 2.5 3 6 3s6-1.5 6-3v-5"/>
      </svg>
    </div>
    <span class="lp-nav-brand-name">TereLearn</span>
  </a>
  <div class="lp-nav-links">
    <a href="#features">Features</a>
    <a href="#how">How It Works</a>
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
      <svg viewBox="0 0 24 24">
        <path d="M22 10v6M2 10l10-5 10 5z"/>
        <path d="M6 12v5c0 1.5 2.5 3 6 3s6-1.5 6-3v-5"/>
      </svg>
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
  <div class="modal-box">
    <h3>Account Recovery</h3>
    <p class="desc">Enter your username or email. We'll send a recovery code via email.</p>
    <div class="alert-box" id="forgotAlert"></div>
    <div class="form-group">
      <label>Username or Email</label>
      <input type="text" id="forgotInput" class="form-control" placeholder="Enter username or email">
    </div>
    <div class="modal-btns">
      <button class="modal-btn secondary" onclick="document.getElementById('forgotModal').classList.remove('active')">Cancel</button>
      <button class="modal-btn primary" id="forgotBtn" onclick="sendRecovery()">Send Recovery Code</button>
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
  if (e) addRipple(document.getElementById('loginBtn'), e);
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  if (!username || !password) { showAlert('credAlert', 'Please fill in all fields.', 'error'); return; }

  hideAlert('credAlert');
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
      setBtn('loginBtn', false, 'Sign in');
      showAlert('credAlert', data.message || 'Invalid username or password.', 'error');
      if (failedAttempts >= MAX_FAILS && data.owner_email) {
        await notifySuspicious(username, data.owner_email, data.owner_phone);
      }
    }
  } catch (err) {
    setBtn('loginBtn', false, 'Sign in');
    showAlert('credAlert', 'Connection error. Please try again.', 'error');
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
  mm.style.color = match ? '#22c55e' : '#ef4444';
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
  document.getElementById('forgotModal').classList.add('active');
}

async function sendRecovery() {
  const val = document.getElementById('forgotInput').value.trim();
  if (!val) { showAlert('forgotAlert', 'Enter your username or email.', 'error'); return; }
  setBtn('forgotBtn', true, 'Sending');
  try {
    const res = await fetch('API/send_otp.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ lookup: val, channel: 'recovery' })
    });
    const data = await res.json();
    if (data.success) {
      showAlert('forgotAlert', `Recovery code sent to ${data.destination}.`, 'success');
    } else {
      showAlert('forgotAlert', data.message || 'Account not found.', 'error');
    }
  } catch (err) {
    showAlert('forgotAlert', 'Error sending recovery code. Try again.', 'error');
  } finally {
    setBtn('forgotBtn', false, 'Send Recovery Code');
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
