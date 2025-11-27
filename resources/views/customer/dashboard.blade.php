<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservations - ShoeVault</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('css/reservation-portal.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
  <style>
    body {
      background: #f8fafc;
      margin: 0;
      padding: 0;
      font-family: 'Montserrat', sans-serif;
      color: #1a202c;
    }
    .reservation-stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:20px; margin:0 0 42px 0; }
    .reservation-stat-card { position:relative; display:flex; align-items:center; gap:16px; padding:1.25rem 1.35rem 1.25rem 1.25rem; border-radius:24px; background:linear-gradient(145deg,rgba(255,255,255,.85) 0%, rgba(245,249,255,.75) 65%, rgba(238,245,255,.65) 100%); backdrop-filter:blur(18px) saturate(180%); -webkit-backdrop-filter:blur(18px) saturate(180%); box-shadow:0 22px 46px -14px rgba(8,32,96,.28), 0 8px 26px -10px rgba(8,32,96,.18); border:1px solid rgba(35,67,206,.20); overflow:hidden; transition:box-shadow .5s cubic-bezier(.21,.8,.32,1), transform .5s cubic-bezier(.21,.8,.32,1); }
    .reservation-stat-card:before { content:''; position:absolute; inset:0; background:linear-gradient(115deg, rgba(2,13,39,.08), rgba(9,44,128,.10) 55%, rgba(42,106,255,.09)); opacity:.55; pointer-events:none; }
    .reservation-stat-card:after { content:''; position:absolute; inset:0; background:repeating-linear-gradient(135deg, rgba(255,255,255,.0) 0px, rgba(255,255,255,.12) 2px, rgba(255,255,255,.0) 4px); mix-blend-mode:overlay; opacity:0; animation:statScan 6.5s linear infinite; pointer-events:none; }
    @keyframes statScan { 0% { opacity:0; transform:translateX(-140%) skewX(-12deg); } 12% { opacity:.55; } 35% { opacity:.18; } 55% { opacity:.35; transform:translateX(140%) skewX(-12deg); } 100% { opacity:0; transform:translateX(140%) skewX(-12deg); } }
    .reservation-stat-card .stat-icon { width:52px; height:52px; border-radius:18px; display:grid; place-items:center; font-size:1.45rem; color:#fff; position:relative; z-index:1; box-shadow:0 12px 28px -10px rgba(0,0,0,.25), 0 4px 14px -6px rgba(0,0,0,.18); }
    .reservation-stat-card .stat-info { position:relative; z-index:1; display:flex; flex-direction:column; }
    .reservation-stat-card .stat-value { font-size:1.75rem; font-weight:800; letter-spacing:.55px; line-height:1.05; background:linear-gradient(90deg,#020d27,#092c80,#2a6aff); -webkit-background-clip:text; color:transparent; filter:drop-shadow(0 2px 6px rgba(42,106,255,.28)); }
    .reservation-stat-card .stat-label { font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; opacity:.72; }
    .reservation-stat-card.stat-pending .stat-icon { background:linear-gradient(145deg,#fde68a,#facc15,#eab308); }
    .reservation-stat-card.stat-completed .stat-icon { background:linear-gradient(145deg,#93c5fd,#3b82f6,#1d4ed8); }
    .reservation-stat-card.stat-cancelled .stat-icon { background:linear-gradient(145deg,#fda4af,#dc2626,#b91c1c); }
    .reservation-stat-card.stat-all .stat-icon { background:linear-gradient(145deg,#c4b5fd,#8b5cf6,#4c1d95); }
    .reservation-stat-card:hover { transform:translateY(-10px) scale(1.02); box-shadow:0 34px 70px -20px rgba(8,32,96,.38), 0 16px 36px -14px rgba(8,32,96,.30); }
    .reservation-stat-card:hover:after { opacity:.55; }
    @media (max-width:780px){ .reservation-stats-grid{ grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:14px; } .reservation-stat-card{ padding:1rem 1.05rem; border-radius:20px; } .reservation-stat-card .stat-icon{ width:46px; height:46px; font-size:1.2rem; } .reservation-stat-card .stat-value{ font-size:1.4rem; } }

    /* Utility: show on mobile only */
    .mobile-only { display: none; }

    /* Dashboard-specific styles that complement the portal design */
    .dashboard-main {
      min-height: 100vh;
      padding-top: 2rem;
      padding-bottom: 2rem;
    }

    .dashboard-container {
      /* full-bleed with 10px side gutters */
      width: calc(100% - 20px);
      max-width: none;
      margin: 0 10px;
      padding: 0 10px;
      box-sizing: border-box;
    }
    
    .dashboard-header {
      background: linear-gradient(120deg, rgba(2,13,39,0.95) 0%, rgba(9,44,128,0.92) 55%, rgba(42,106,255,0.85) 100%);
      color: #fff;
      padding: 3.2rem 2.4rem;
      border-radius: 26px;
      margin-bottom: 2.4rem;
      text-align: center;
      position: relative;
      overflow: hidden;
      box-shadow: 0 24px 54px -12px rgba(8,32,96,0.55), 0 8px 24px -8px rgba(5,20,60,0.45);
      backdrop-filter: blur(14px) saturate(160%);
      -webkit-backdrop-filter: blur(14px) saturate(160%);
      border: 1px solid rgba(255,255,255,0.10);
    }

    .dashboard-header::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(115deg, transparent 0%, rgba(255,255,255,0.08) 45%, transparent 60%);
      animation: headerSheen 5.5s cubic-bezier(.25,.8,.35,1) infinite;
      pointer-events: none;
    }
    .dashboard-header::after {
      content: '';
      position: absolute;
      top: -30%; left: -10%; width: 220px; height: 220px;
      background: radial-gradient(circle at center, rgba(42,106,255,0.55), rgba(42,106,255,0));
      filter: blur(70px);
      opacity: .35;
      animation: headerAura 7.2s ease-in-out infinite;
      pointer-events: none;
    }

    @keyframes headerSheen { 0% { transform: translateX(-120%); } 55% { transform: translateX(140%); } 100% { transform: translateX(140%); } }
    @keyframes headerAura { 0%,100% { transform: scale(.85) translate(0,0); opacity:.25; } 50% { transform: scale(1.15) translate(18px,12px); opacity:.45; } }

    .dashboard-header h1 {
      font-size: 2.4rem;
      font-weight: 800;
      margin: 0 0 .65rem 0;
      position: relative;
      z-index: 1;
      letter-spacing: .6px;
      display:flex; align-items:center; justify-content:center; gap:.6rem;
      text-shadow: 0 4px 18px rgba(0,0,0,0.4), 0 2px 8px rgba(42,106,255,0.35);
    }

    .dashboard-header p {
      font-size: 1.05rem;
      opacity: .88;
      margin: 0;
      position: relative;
      z-index: 1;
      max-width: 720px;
      margin-inline: auto;
    }
    
    .dashboard-card {
      background: linear-gradient(145deg,#ffffff 0%, #f4f7ff 100%);
      border-radius: 24px;
      box-shadow: 0 10px 28px rgba(8,32,96,0.06), 0 6px 16px rgba(8,32,96,0.05);
      overflow: hidden;
      transition: box-shadow .35s ease, transform .35s ease, border-color .35s ease;
      border: 1px solid rgba(8,32,96,0.08);
      position:relative;
    }
    .dashboard-card:before { content:''; position:absolute; inset:0; background: linear-gradient(115deg, rgba(2,13,39,0.06), rgba(9,44,128,0.08) 55%, rgba(42,106,255,0.07)); opacity:.55; pointer-events:none; }
    
    .dashboard-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 28px 60px -12px rgba(8,32,96,0.35), 0 10px 24px -8px rgba(5,20,60,0.25);
      border-color: rgba(42,106,255,0.35);
    }
    
    .card-header {
      background: linear-gradient(120deg, rgba(2,13,39,0.96) 0%, rgba(9,44,128,0.94) 55%, rgba(42,106,255,0.88) 100%);
      color: #fff;
      padding: 1.85rem 2rem 1.6rem;
      font-weight: 700;
      font-size: 1.18rem;
      position: relative;
      overflow: hidden;
      border-bottom: 1px solid rgba(255,255,255,0.12);
      backdrop-filter: blur(10px);
    }
    .card-header:before { content:''; position:absolute; inset:0; background: linear-gradient(110deg, transparent 0%, rgba(255,255,255,0.10) 45%, transparent 60%); animation: headerSheen 6s cubic-bezier(.25,.8,.35,1) infinite; pointer-events:none; }

    .card-header::after { content:''; position:absolute; top:-25%; right:-10%; width:160px; height:160px; background: radial-gradient(circle at center, rgba(42,106,255,0.55), rgba(42,106,255,0)); filter: blur(60px); opacity:.4; }
    
    .card-content {
      padding: 2rem;
    }
    
    .tab-navigation {
      display: flex;
      background: linear-gradient(145deg,#ffffff 0%, #f2f6ff 100%);
      border-radius: 18px;
      padding: 0.6rem 0.6rem;
      margin-bottom: 2.2rem;
      gap: 0.6rem;
      box-shadow: 0 6px 18px rgba(8,32,96,0.07);
      border:1px solid rgba(8,32,96,0.08);
      position:relative;
    }
    .tab-navigation:before { content:''; position:absolute; inset:0; background: linear-gradient(115deg, rgba(2,13,39,0.04), rgba(9,44,128,0.06) 55%, rgba(42,106,255,0.05)); opacity:.6; pointer-events:none; border-radius:inherit; }
    
    .tab-btn {
      flex: 1; padding: .85rem 1.25rem; border:none; background: rgba(9,44,128,0.06); color:#3b4e6d; font-weight:600; cursor:pointer; transition: all .35s cubic-bezier(.21,.8,.32,1); border-radius:14px; font-size:.85rem; display:flex; align-items:center; justify-content:center; gap:.55rem; position:relative; overflow:hidden;
    }
    .tab-btn i { color: #092c80; transition: color .35s ease, transform .35s ease; }
    
    .tab-btn:hover { color:#092c80; background: rgba(9,44,128,0.12); box-shadow:0 4px 14px -6px rgba(8,32,96,.25); }
    
    .tab-btn.active {
      color:#fff; background: linear-gradient(120deg, #020d27 0%, #092c80 70%, #2a6aff 100%); box-shadow:0 10px 28px -10px rgba(9,44,128,.55), 0 4px 12px -6px rgba(9,44,128,.35); transform: translateY(-2px); }
    .tab-btn.active i { color:#fff; }
    .tab-btn.active:before { content:''; position:absolute; inset:0; background: linear-gradient(110deg, transparent 0%, rgba(255,255,255,0.12) 45%, transparent 60%); animation: headerSheen 6.2s cubic-bezier(.25,.8,.35,1) infinite; pointer-events:none; }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }
    
    .reservation-item, .transaction-item {
      background: linear-gradient(135deg,#ffffff 0%, #f4f7ff 100%);
      border-radius: 18px;
      padding: 1.45rem 1.55rem 1.55rem;
      margin-bottom: 1.55rem;
      border-left: 6px solid #092c80;
      transition: box-shadow .35s ease, transform .35s ease, border-color .35s ease;
      position: relative;
      overflow: hidden;
      box-shadow: 0 6px 16px rgba(8,32,96,0.07);
    }
    .reservation-item:before { content:''; position:absolute; inset:0; background: linear-gradient(115deg, rgba(2,13,39,0.04), rgba(9,44,128,0.08) 55%, rgba(42,106,255,0.05)); opacity:.6; pointer-events:none; }

    .reservation-item::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent 0%, rgba(66, 153, 225, 0.5) 50%, transparent 100%);
    }
    
    .reservation-item:hover { transform: translateY(-6px); box-shadow:0 18px 44px -12px rgba(8,32,96,0.25); border-color:#2a6aff; }
    
    .reservation-item.cancelled { border-left-color:#c53030; background: linear-gradient(135deg,#fff5f5 0%, #fee2e2 100%); }
    
    .reservation-item.completed { border-left-color:#2563eb; background: linear-gradient(135deg,#eff6ff 0%, #dbeafe 100%); }
    
    .status-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.45rem .9rem; border-radius:999px; font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.65px; position:relative; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
    .status-badge:before { content:''; position:absolute; inset:0; background: linear-gradient(110deg, transparent 0%, rgba(255,255,255,0.25) 50%, transparent 70%); animation: headerSheen 5.8s linear infinite; }
    
    .status-pending { background: linear-gradient(135deg,#fff9e6 0%, #ffe2a8 100%); color:#b7791f; border:1px solid rgba(183,121,31,.45); }
    
    .status-confirmed { background: linear-gradient(135deg,#e6f9ff 0%, #c1ecff 100%); color:#075985; border:1px solid rgba(7,89,133,.45); }
    
    .status-completed { background: linear-gradient(135deg,#edf7ff 0%, #d6edff 100%); color:#1e3a8a; border:1px solid rgba(30,58,138,.45); }
    
    .status-cancelled { background: linear-gradient(135deg,#ffe5e5 0%, #ffcaca 100%); color:#c53030; border:1px solid rgba(197,48,48,.45); }
    
    .status-for_cancellation { background: linear-gradient(135deg,#fff3d1 0%, #ffe3a1 100%); color:#b45309; border:1px solid rgba(180,83,9,.45); }
    
    .action-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    
    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }
    
    .btn-primary { background: linear-gradient(120deg, #020d27 0%, #092c80 70%, #2a6aff 100%); color:#fff; box-shadow:0 8px 22px -6px rgba(9,44,128,.55), 0 4px 12px -4px rgba(9,44,128,.35); border:1px solid rgba(255,255,255,0.14); }
    
    .btn-primary:hover { transform: translateY(-4px); box-shadow:0 16px 40px -12px rgba(9,44,128,.55), 0 8px 24px -8px rgba(9,44,128,.45); filter:brightness(1.06); }
    
    .btn-danger { background: linear-gradient(135deg,#dc2626 0%, #b91c1c 100%); color:#fff; box-shadow:0 8px 22px -8px rgba(220,38,38,.45); border:1px solid rgba(220,38,38,.35); }
    
    .btn-danger:hover { transform: translateY(-4px); box-shadow:0 18px 44px -14px rgba(220,38,38,.55); filter:brightness(1.06); }
    
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      color: #718096;
    }
    
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 1.5rem;
      opacity: 0.3;
      background: linear-gradient(135deg, #4299e1, #9f7aea);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .empty-state h3 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: #2d3748;
    }

    .empty-state p {
      font-size: 1rem;
      margin-bottom: 2rem;
      opacity: 0.7;
    }
    
    @media (max-width: 768px) {
      .dashboard-container {
        padding: 0 1rem;
      }

      /* reveal mobile-only blocks */
      .mobile-only { display: flex; flex-direction: column; align-items: center; text-align: center; }

      .dashboard-header {
        padding: 2rem 1.5rem;
        margin-bottom: 1.5rem;
      }

      .dashboard-header h1 {
        font-size: 2rem;
      }
      
      .tab-navigation {
        flex-wrap: wrap;
        gap: 0.25rem;
        padding: 0.25rem;
      }
      
      .tab-btn {
        flex: 1 1 calc(50% - 0.125rem);
        padding: 0.75rem 1rem;
        font-size: 0.8rem;
      }

      .card-content {
        padding: 1.5rem;
      }

      .reservation-item, .transaction-item {
        padding: 1.25rem;
        margin-bottom: 1rem;
      }

      .action-buttons {
        justify-content: center;
        gap: 0.75rem;
      }

      .btn {
        justify-content: center;
      }
    }

    @media (max-width: 480px) {
      .tab-btn {
        flex: 1 1 100%;
        margin-bottom: 0.25rem;
      }
    }

    /* Additional sv-user styles to ensure dropdown works */
    .sv-user { position: relative; }
    .sv-user-menu { position:absolute; top:110%; right:0; width:min(300px,80vw); padding:16px 16px 14px; border-radius:22px; background:linear-gradient(145deg,rgba(255,255,255,.92) 0%, rgba(245,249,255,.88) 65%, rgba(238,245,255,.82) 100%); backdrop-filter:blur(22px) saturate(180%); -webkit-backdrop-filter:blur(22px) saturate(180%); border:1px solid rgba(35,67,206,.18); box-shadow:0 30px 54px -18px rgba(8,32,96,.35), 0 12px 32px -12px rgba(8,32,96,.28); display:none; transform-origin:top right; overflow:hidden; }
    .sv-user-menu:before { content:''; position:absolute; inset:0; background:linear-gradient(115deg,rgba(2,13,39,.05),rgba(9,44,128,.09) 55%,rgba(42,106,255,.08)); pointer-events:none; }
    .sv-user-menu:after { content:''; position:absolute; inset:0; background:radial-gradient(circle at top right, rgba(42,106,255,.35), transparent 70%); mix-blend-mode:overlay; opacity:.55; pointer-events:none; }
    .sv-user-menu.open { display:block !important; animation:userMenuIn .42s cubic-bezier(.22,1.2,.4,1); }
    @keyframes userMenuIn { 0% { opacity:0; transform:translateY(-12px) scale(.94); } 55% { opacity:1; transform:translateY(4px) scale(1.02); } 100% { opacity:1; transform:translateY(0) scale(1); } }
    .sv-user-item { display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:14px; font-size:.78rem; font-weight:700; letter-spacing:.5px; color:#0f172a; background:linear-gradient(135deg,rgba(255,255,255,.65),rgba(245,249,255,.55)); text-decoration:none; transition:background .35s ease, transform .25s ease, box-shadow .35s ease; position:relative; overflow:hidden; }
    .sv-user-item:before { content:''; position:absolute; inset:0; background:linear-gradient(110deg, transparent 0%, rgba(255,255,255,.28) 50%, transparent 70%); opacity:0; transition:opacity .5s ease; }
    .sv-user-item:hover { background:linear-gradient(135deg,rgba(255,255,255,.85),rgba(245,249,255,.75)); box-shadow:0 10px 24px -8px rgba(8,32,96,.25); transform:translateY(-2px); }
    .sv-user-item:hover:before { opacity:1; }
    .sv-user-item.danger { background:linear-gradient(135deg,rgba(255,245,245,.70),rgba(255,230,230,.65)); color:#b91c1c; border:1px solid rgba(220,38,38,.25); }
    .sv-user-item.danger:hover { background:linear-gradient(135deg,rgba(255,230,230,.92),rgba(255,245,245,.85)); box-shadow:0 12px 26px -10px rgba(220,38,38,.45); }
    
    @keyframes sv-fade-in-up {
      0% {
        opacity: 0;
        transform: translateY(10px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Cancel Confirmation Modal */
    .cancel-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      animation: fadeIn 0.3s ease;
    }

    .cancel-modal.show {
      display: flex;
    }

    .cancel-modal-content {
      background: white;
      border-radius: 20px;
      padding: 2rem;
      max-width: 450px;
      width: 90%;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
      position: relative;
      animation: slideIn 0.3s ease;
    }

    .cancel-modal-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .cancel-modal-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #e53e3e;
      font-size: 1.5rem;
    }

    .cancel-modal-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: #2d3748;
      margin: 0;
    }

    .cancel-modal-text {
      color: #4a5568;
      margin-bottom: 2rem;
      line-height: 1.6;
    }

    .cancel-modal-reservation {
      background: #f7fafc;
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      border-left: 4px solid #e53e3e;
    }

    .cancel-modal-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
    }

    .modal-btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .modal-btn-cancel {
      background: #f7fafc;
      color: #4a5568;
      border: 1px solid #e2e8f0;
    }

    .modal-btn-cancel:hover {
      background: #edf2f7;
      transform: translateY(-1px);
    }

    .modal-btn-confirm {
      background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(229, 62, 62, 0.4);
    }

    .modal-btn-confirm:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(229, 62, 62, 0.6);
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* Collapsible Items Styles */
    .reservation-items-toggle {
      background: rgba(66, 153, 225, 0.1);
      border: 1px solid rgba(66, 153, 225, 0.3);
      border-radius: 8px;
      padding: 0.75rem 1rem;
      margin-top: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.875rem;
      font-weight: 600;
      color: #3182ce;
    }

    .reservation-items-toggle:hover {
      background: rgba(66, 153, 225, 0.15);
      border-color: rgba(66, 153, 225, 0.5);
    }

    .reservation-items-toggle i {
      transition: transform 0.3s ease;
    }

    .reservation-items-toggle.expanded i {
      transform: rotate(180deg);
    }

    .reservation-items-list {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease, padding 0.3s ease;
      background: #f8fafc;
      border-radius: 0 0 8px 8px;
      margin-top: -1px;
      border: 1px solid rgba(66, 153, 225, 0.2);
      border-top: none;
    }

    .reservation-items-list.expanded {
      max-height: 500px;
      padding: 1rem;
    }

    .reservation-single-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.75rem 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .reservation-single-item:last-child {
      border-bottom: none;
    }

    .item-image {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      background: #edf2f7;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      flex-shrink: 0;
    }

    .item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .item-image-placeholder {
      color: #a0aec0;
      font-size: 1.25rem;
    }

    .item-details {
      flex: 1;
      min-width: 0;
    }

    .item-name {
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 0.25rem;
      font-size: 0.875rem;
    }

    .item-specs {
      font-size: 0.75rem;
      color: #718096;
      margin-bottom: 0.25rem;
    }

    .item-price-qty {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.8rem;
    }

    .item-price {
      font-weight: 600;
      color: #3182ce;
    }

    .item-qty {
      color: #718096;
    }

    /* Image Preview Modal */
    .image-preview-modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.8);
      animation: fadeIn 0.3s ease;
    }

    .image-preview-modal.show {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .image-preview-content {
      position: relative;
      max-width: 90%;
      max-height: 90%;
      animation: zoomIn 0.3s ease;
    }

    .image-preview-content img {
      width: 100%;
      height: auto;
      border-radius: 8px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    .image-preview-close {
      position: absolute;
      top: -40px;
      right: 0;
      background: rgba(255, 255, 255, 0.9);
      border: none;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      font-size: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #333;
      transition: all 0.2s ease;
    }

    .image-preview-close:hover {
      background: #fff;
      transform: scale(1.1);
    }

    .item-image img {
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .item-image img:hover {
      transform: scale(1.05);
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes zoomIn {
      from { transform: scale(0.5); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
  </style>
</head>
<body>
  <!-- Navigation matching portal style -->
  <nav class="res-portal-navbar">
    <div class="nav-left" style="display:flex;align-items:center;margin-right:auto;gap:12px;">
      <a href="{{ route('reservation.home') }}" class="res-portal-logo-link">
        <img src="{{ asset('reservation-assets/shoevault-logo.png') }}" alt="ShoeVault Logo" class="res-portal-logo">
      </a>
      <!-- Inline dashboard title (moved from main header). Desktop only. -->
      <div class="navbar-dashboard-info desktop-only" style="display:flex;margin-left:6px;align-items:center;gap:12px;">
        <div class="nav-dashboard-title" style="display:flex;align-items:center;gap:6px;font-weight:700;color:#fff;font-size:1.05rem;letter-spacing:.4px;">
          <i class="fas fa-calendar-check" style="font-size:1rem;"></i>
          Reservations
        </div>
        <div class="nav-dashboard-sub" style="font-size:.75rem;font-weight:600;color:rgba(255,255,255,0.75);padding:.25rem .6rem;border:1px solid rgba(255,255,255,.18);border-radius:8px;background:rgba(255,255,255,.08);backdrop-filter:blur(6px);">Welcome, {{ $customer->fullname }}</div>
      </div>
    </div>
    <div class="cart-container" style="display:flex;align-items:center;gap:10px;">
      <a href="{{ route('reservation.portal') }}" class="res-portal-nav-btn desktop-only" style="text-decoration: none;margin-right:10px;">
        <span class="nav-icon"><i class="fas fa-store"></i></span>
        <span class="nav-label">Products</span>
      </a>
      <div class="user-status-container">
        <div class="sv-user">
          <button class="sv-user-btn" type="button" aria-expanded="false" aria-haspopup="menu">
            <span class="sv-user-avatar">{{ strtoupper(substr(($customer->username ?? $customer->fullname ?? $customer->name ?? $customer->email ?? 'C'), 0, 1)) }}</span>
            <span class="sv-user-name">{{ $customer->username ?? $customer->first_name ?? $customer->name ?? $customer->fullname ?? 'Customer' }}</span>
            <i class="fas fa-chevron-down" style="font-size:0.75rem;"></i>
          </button>
          <div class="sv-user-menu" role="menu">
            <div class="sv-user-meta">
              <div class="sv-user-email">{{ $customer->email }}</div>
            </div>
            <a href="{{ route('reservation.portal') }}" class="sv-user-item" role="menuitem">
              <i class="fas fa-store"></i>
              <span>Products</span>
            </a>
            <div class="sv-user-divider" style="height: 1px; background-color: #e2e8f0; margin: 8px 0;"></div>
            <form method="POST" action="{{ route('customer.logout') }}">
              @csrf
              <button type="submit" class="sv-user-item danger" role="menuitem">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="dashboard-main" style="padding-top: 150px;">
    <div class="dashboard-container">
    <!-- Mobile header restored (visible on small screens only) -->
    <div class="dashboard-header mobile-only" style="margin-bottom:1.4rem;">
      <h1><i class="fas fa-calendar-check"></i> Reservations</h1>
      <p>Review, track and manage all your reservations</p>
    </div>

    <!-- Reservation Stats Summary -->
    @php
      $pendingCount = count($pendingReservations);
      $completedCount = count($completedReservations);
      $cancelledCount = count($cancelledReservations);
      $allCount = count($allReservations);
    @endphp
    <div class="reservation-stats-grid" aria-label="Reservation summary">
      <div class="reservation-stat-card stat-pending" role="group" aria-label="Pending reservations">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
          <div class="stat-value">{{ $pendingCount }}</div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
      <div class="reservation-stat-card stat-completed" role="group" aria-label="Completed reservations">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
          <div class="stat-value">{{ $completedCount }}</div>
          <div class="stat-label">Completed</div>
        </div>
      </div>
      <div class="reservation-stat-card stat-cancelled" role="group" aria-label="Cancelled reservations">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
          <div class="stat-value">{{ $cancelledCount }}</div>
          <div class="stat-label">Cancelled</div>
        </div>
      </div>
      <div class="reservation-stat-card stat-all" role="group" aria-label="Total reservations">
        <div class="stat-icon"><i class="fas fa-list"></i></div>
        <div class="stat-info">
          <div class="stat-value">{{ $allCount }}</div>
          <div class="stat-label">Total</div>
        </div>
      </div>
    </div>

    <!-- Reservations Card with Tabs -->
    <div class="dashboard-card">
      <div class="card-header">
        <i class="fas fa-calendar-alt"></i> Reservations
      </div>
      <div class="card-content">
        <!-- Tab Navigation -->
        <div class="tab-navigation">
          <button class="tab-btn active" onclick="switchTab('pending')" id="pending-tab">
            <i class="fas fa-clock"></i> Pending ({{ count($pendingReservations) }})
          </button>
          <button class="tab-btn" onclick="switchTab('completed')" id="completed-tab">
            <i class="fas fa-check-circle"></i> Completed ({{ count($completedReservations) }})
          </button>
          <button class="tab-btn" onclick="switchTab('cancelled')" id="cancelled-tab">
            <i class="fas fa-times-circle"></i> Cancelled ({{ count($cancelledReservations) }})
          </button>
          <button class="tab-btn" onclick="switchTab('all')" id="all-tab">
            <i class="fas fa-list"></i> All ({{ count($allReservations) }})
          </button>
        </div>

        <!-- Pending Reservations Tab Content -->
        <div class="tab-content active" id="pending-content">
          @if(count($pendingReservations) > 0)
            @foreach($pendingReservations as $reservation)
              <div class="reservation-item {{ $reservation->status }}" data-reservation-id="{{ $reservation->reservation_id }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <strong>{{ $reservation->reservation_id }}</strong>
                  <span class="status-badge status-{{ $reservation->status }}">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                </div>
                <p><i class="fas fa-calendar"></i> Reserved: {{ $reservation->created_at->format('M d, Y') }}</p>
                @if($reservation->pickup_date)
                  <p><i class="fas fa-clock"></i> Pickup: {{ $reservation->pickup_date->format('M d, Y') }} at {{ \Carbon\Carbon::createFromFormat('H:i:s', $reservation->pickup_time)->format('g:i A') }}</p>
                @endif
                <p><i class="fas fa-peso-sign"></i> Total: ₱{{ number_format($reservation->total_amount, 2) }}</p>
                
                <!-- Collapsible Items Toggle -->
                <div class="reservation-items-toggle" onclick="toggleReservationItems('{{ $reservation->reservation_id }}')"
                     id="toggle-{{ $reservation->reservation_id }}">
                  @php 
                    $itemsData = is_string($reservation->items) ? json_decode($reservation->items, true) : $reservation->items;
                    $itemCount = is_array($itemsData) ? count($itemsData) : 0;
                  @endphp
                  <span><i class="fas fa-box"></i> View Reserved Items ({{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }})</span>
                  <i class="fas fa-chevron-down"></i>
                </div>
                
                <!-- Collapsible Items List -->
                <div class="reservation-items-list" id="items-{{ $reservation->reservation_id }}">
                  @if(is_string($reservation->items))
                    @php $items = json_decode($reservation->items, true); @endphp
                  @else
                    @php $items = $reservation->items; @endphp
                  @endif
                  
                  @if($items && is_array($items))
                    @foreach($items as $item)
                      <div class="reservation-single-item">
                        <div class="item-image">
                          @php
                            $imageId = $item['product_size_id'] ?? $item['product_id'] ?? null;
                          @endphp
                          @if($imageId)
                            <img src="{{ asset(\App\Models\Reservation::getItemImageUrl($imageId)) }}" alt="{{ $item['product_name'] ?? 'Product' }}" onclick="showImagePreview(this.src, '{{ $item['product_name'] ?? 'Product' }}')">
                          @else
                            <div class="item-image-placeholder">
                              <i class="fas fa-shoe-prints"></i>
                            </div>
                          @endif
                        </div>
                        <div class="item-details">
                          <div class="item-name">{{ $item['product_name'] ?? 'Product' }}</div>
                          <div class="item-specs">
                            {{ $item['product_brand'] ?? 'Unknown Brand' }} • 
                            Size {{ $item['product_size'] ?? $item['size'] ?? 'N/A' }} • 
                            {{ $item['product_color'] ?? 'Unknown Color' }}
                          </div>
                          <div class="item-price-qty">
                            <span class="item-price">₱{{ number_format($item['product_price'] ?? 0, 2) }}</span>
                            <span class="item-qty">Qty: {{ $item['quantity'] ?? 1 }}</span>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
                
                @if(in_array($reservation->status, ['pending', 'for_cancellation']))
                  <div class="action-buttons">
                    <button class="btn btn-danger" onclick="cancelReservation('{{ $reservation->reservation_id }}')">
                      <i class="fas fa-times"></i> Cancel
                    </button>
                  </div>
                @endif
              </div>
            @endforeach
          @else
            <div class="empty-state">
              <i class="fas fa-clock"></i>
              <h3>No Pending Reservations</h3>
              <p>You don't have any pending reservations.</p>
              <a href="{{ route('reservation.portal') }}" class="btn btn-primary">Make a Reservation</a>
            </div>
          @endif
        </div>

        <!-- All Reservations Tab Content -->
        <div class="tab-content" id="all-content">
          @if(count($allReservations) > 0)
            @foreach($allReservations as $reservation)
              <div class="reservation-item" data-reservation-id="{{ $reservation->reservation_id }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <strong>{{ $reservation->reservation_id }}</strong>
                  <span class="status-badge status-{{ $reservation->status }}">{{ ucfirst(str_replace('_', ' ', $reservation->status)) }}</span>
                </div>
                <p><i class="fas fa-calendar"></i> Reserved: {{ $reservation->created_at->format('M d, Y') }}</p>
                @if($reservation->pickup_date)
                  <p><i class="fas fa-clock"></i> Pickup: {{ $reservation->pickup_date->format('M d, Y') }} at {{ \Carbon\Carbon::createFromFormat('H:i:s', $reservation->pickup_time)->format('g:i A') }}</p>
                @endif
                <p><i class="fas fa-peso-sign"></i> Total: ₱{{ number_format($reservation->total_amount, 2) }}</p>
                
                <!-- Collapsible Items Toggle -->
                <div class="reservation-items-toggle" onclick="toggleReservationItems('{{ $reservation->reservation_id }}-pending')"
                     id="toggle-{{ $reservation->reservation_id }}-pending">
                  @php 
                    $itemsData = is_string($reservation->items) ? json_decode($reservation->items, true) : $reservation->items;
                    $itemCount = is_array($itemsData) ? count($itemsData) : 0;
                  @endphp
                  <span><i class="fas fa-box"></i> View Reserved Items ({{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }})</span>
                  <i class="fas fa-chevron-down"></i>
                </div>
                
                <!-- Collapsible Items List -->
                <div class="reservation-items-list" id="items-{{ $reservation->reservation_id }}-pending">
                  @if(is_string($reservation->items))
                    @php $items = json_decode($reservation->items, true); @endphp
                  @else
                    @php $items = $reservation->items; @endphp
                  @endif
                  
                  @if($items && is_array($items))
                    @foreach($items as $item)
                      <div class="reservation-single-item">
                        <div class="item-image">
                          @php
                            $imageId = $item['product_size_id'] ?? $item['product_id'] ?? null;
                          @endphp
                          @if($imageId)
                            <img src="{{ asset(\App\Models\Reservation::getItemImageUrl($imageId)) }}" alt="{{ $item['product_name'] ?? 'Product' }}" onclick="showImagePreview(this.src, '{{ $item['product_name'] ?? 'Product' }}')">
                          @else
                            <div class="item-image-placeholder">
                              <i class="fas fa-shoe-prints"></i>
                            </div>
                          @endif
                        </div>
                        <div class="item-details">
                          <div class="item-name">{{ $item['product_name'] ?? 'Product' }}</div>
                          <div class="item-specs">
                            {{ $item['product_brand'] ?? 'Unknown Brand' }} • 
                            Size {{ $item['product_size'] ?? $item['size'] ?? 'N/A' }} • 
                            {{ $item['product_color'] ?? 'Unknown Color' }}
                          </div>
                          <div class="item-price-qty">
                            <span class="item-price">₱{{ number_format($item['product_price'] ?? 0, 2) }}</span>
                            <span class="item-qty">Qty: {{ $item['quantity'] ?? 1 }}</span>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
                
                @if(in_array($reservation->status, ['pending', 'for_cancellation']))
                  <div class="action-buttons">
                    <button class="btn btn-danger" onclick="cancelReservation('{{ $reservation->reservation_id }}')">
                      <i class="fas fa-times"></i> Cancel
                    </button>
                  </div>
                @endif
              </div>
            @endforeach
          @else
            <div class="empty-state">
              <i class="fas fa-list"></i>
              <h3>No Reservations</h3>
              <p>You haven't made any reservations yet.</p>
              <a href="{{ route('reservation.portal') }}" class="btn btn-primary">Make a Reservation</a>
            </div>
          @endif
        </div>

        <!-- Completed Reservations Tab Content -->
        <div class="tab-content" id="completed-content">
          @if(count($completedReservations) > 0)
            @foreach($completedReservations as $reservation)
              <div class="reservation-item completed" data-reservation-id="{{ $reservation->reservation_id }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <strong>{{ $reservation->reservation_id }}</strong>
                  <span class="status-badge status-completed">Completed</span>
                </div>
                <p><i class="fas fa-calendar"></i> Reserved: {{ $reservation->created_at->format('M d, Y') }}</p>
                @if($reservation->pickup_date)
                  <p><i class="fas fa-check"></i> Completed: {{ $reservation->pickup_date->format('M d, Y') }}</p>
                @endif
                <p><i class="fas fa-peso-sign"></i> Total: ₱{{ number_format($reservation->total_amount, 2) }}</p>
                
                <!-- Collapsible Items Toggle -->
                <div class="reservation-items-toggle" onclick="toggleReservationItems('{{ $reservation->reservation_id }}-completed')"
                     id="toggle-{{ $reservation->reservation_id }}-completed">
                  @php 
                    $itemsData = is_string($reservation->items) ? json_decode($reservation->items, true) : $reservation->items;
                    $itemCount = is_array($itemsData) ? count($itemsData) : 0;
                  @endphp
                  <span><i class="fas fa-box"></i> View Items Purchased ({{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }})</span>
                  <i class="fas fa-chevron-down"></i>
                </div>
                
                <!-- Collapsible Items List -->
                <div class="reservation-items-list" id="items-{{ $reservation->reservation_id }}-completed">
                  @if(is_string($reservation->items))
                    @php $items = json_decode($reservation->items, true); @endphp
                  @else
                    @php $items = $reservation->items; @endphp
                  @endif
                  
                  @if($items && is_array($items))
                    @foreach($items as $item)
                      <div class="reservation-single-item">
                        <div class="item-image">
                          @php
                            $imageId = $item['product_size_id'] ?? $item['product_id'] ?? null;
                          @endphp
                          @if($imageId)
                            <img src="{{ asset(\App\Models\Reservation::getItemImageUrl($imageId)) }}" alt="{{ $item['product_name'] ?? 'Product' }}" onclick="showImagePreview(this.src, '{{ $item['product_name'] ?? 'Product' }}')">
                          @else
                            <div class="item-image-placeholder">
                              <i class="fas fa-shoe-prints"></i>
                            </div>
                          @endif
                        </div>
                        <div class="item-details">
                          <div class="item-name">{{ $item['product_name'] ?? 'Product' }}</div>
                          <div class="item-specs">
                            {{ $item['product_brand'] ?? 'Unknown Brand' }} • 
                            Size {{ $item['product_size'] ?? $item['size'] ?? 'N/A' }} • 
                            {{ $item['product_color'] ?? 'Unknown Color' }}
                          </div>
                          <div class="item-price-qty">
                            <span class="item-price">₱{{ number_format($item['product_price'] ?? 0, 2) }}</span>
                            <span class="item-qty">Qty: {{ $item['quantity'] ?? 1 }}</span>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
              </div>
            @endforeach
          @else
            <div class="empty-state">
              <i class="fas fa-check-circle"></i>
              <h3>No Completed Reservations</h3>
              <p>Your completed reservations will appear here.</p>
            </div>
          @endif
        </div>

        <!-- Cancelled Reservations Tab Content -->
        <div class="tab-content" id="cancelled-content">
          @if(count($cancelledReservations) > 0)
            @foreach($cancelledReservations as $reservation)
              <div class="reservation-item cancelled" data-reservation-id="{{ $reservation->reservation_id }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <strong>{{ $reservation->reservation_id }}</strong>
                  <span class="status-badge status-cancelled">Cancelled</span>
                </div>
                <p><i class="fas fa-calendar"></i> Reserved: {{ $reservation->created_at->format('M d, Y') }}</p>
                @if($reservation->pickup_date)
                  <p><i class="fas fa-times"></i> Was scheduled for: {{ $reservation->pickup_date->format('M d, Y') }}</p>
                @endif
                <p><i class="fas fa-peso-sign"></i> Total: ₱{{ number_format($reservation->total_amount, 2) }}</p>
                
                <!-- Collapsible Items Toggle -->
                <div class="reservation-items-toggle" onclick="toggleReservationItems('{{ $reservation->reservation_id }}-cancelled')"
                     id="toggle-{{ $reservation->reservation_id }}-cancelled">
                  @php 
                    $itemsData = is_string($reservation->items) ? json_decode($reservation->items, true) : $reservation->items;
                    $itemCount = is_array($itemsData) ? count($itemsData) : 0;
                  @endphp
                  <span><i class="fas fa-box"></i> View Cancelled Items ({{ $itemCount }} item{{ $itemCount > 1 ? 's' : '' }})</span>
                  <i class="fas fa-chevron-down"></i>
                </div>
                
                <!-- Collapsible Items List -->
                <div class="reservation-items-list" id="items-{{ $reservation->reservation_id }}-cancelled">
                  @if(is_string($reservation->items))
                    @php $items = json_decode($reservation->items, true); @endphp
                  @else
                    @php $items = $reservation->items; @endphp
                  @endif
                  
                  @if($items && is_array($items))
                    @foreach($items as $item)
                      <div class="reservation-single-item">
                        <div class="item-image">
                          @php
                            $imageId = $item['product_size_id'] ?? $item['product_id'] ?? null;
                          @endphp
                          @if($imageId)
                            <img src="{{ asset(\App\Models\Reservation::getItemImageUrl($imageId)) }}" alt="{{ $item['product_name'] ?? 'Product' }}" onclick="showImagePreview(this.src, '{{ $item['product_name'] ?? 'Product' }}')">
                          @else
                            <div class="item-image-placeholder">
                              <i class="fas fa-shoe-prints"></i>
                            </div>
                          @endif
                        </div>
                        <div class="item-details">
                          <div class="item-name">{{ $item['product_name'] ?? 'Product' }}</div>
                          <div class="item-specs">
                            {{ $item['product_brand'] ?? 'Unknown Brand' }} • 
                            Size {{ $item['product_size'] ?? $item['size'] ?? 'N/A' }} • 
                            {{ $item['product_color'] ?? 'Unknown Color' }}
                          </div>
                          <div class="item-price-qty">
                            <span class="item-price">₱{{ number_format($item['product_price'] ?? 0, 2) }}</span>
                            <span class="item-qty">Qty: {{ $item['quantity'] ?? 1 }}</span>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
              </div>
            @endforeach
          @else
            <div class="empty-state">
              <i class="fas fa-times-circle"></i>
              <h3>No Cancelled Reservations</h3>
              <p>Your cancelled reservations will appear here.</p>
            </div>
          @endif
        </div>
      </div>
    </div>


    </div>
  </div>

  <!-- Image Preview Modal -->
  <div class="image-preview-modal" id="imagePreviewModal">
    <div class="image-preview-content">
      <button class="image-preview-close" onclick="closeImagePreview()">&times;</button>
      <img id="previewImage" src="" alt="Product Preview">
    </div>
  </div>

  <!-- Cancel Confirmation Modal -->
  <div class="cancel-modal" id="cancelModal">
    <div class="cancel-modal-content">
      <div class="cancel-modal-header">
        <div class="cancel-modal-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="cancel-modal-title">Cancel Reservation</h3>
      </div>
      <p class="cancel-modal-text">
        Are you sure you want to cancel this reservation? This action cannot be undone.
      </p>
      <div class="cancel-modal-reservation" id="modalReservationInfo">
        <!-- Reservation details will be inserted here -->
      </div>
      <div class="cancel-modal-actions">
        <button class="modal-btn modal-btn-cancel" onclick="closeCancelModal()">
          <i class="fas fa-times"></i> Keep Reservation
        </button>
        <button class="modal-btn modal-btn-confirm" id="confirmCancelBtn">
          <i class="fas fa-trash"></i> Yes, Cancel It
        </button>
      </div>
    </div>
  </div>

  <script>
    let currentReservationId = null;
    let isCancelling = false; // Flag to prevent duplicate calls

    function cancelReservation(reservationId) {
      currentReservationId = reservationId;
      
      // Find the reservation element to get details
      const reservationElements = document.querySelectorAll('.reservation-item');
      let reservationInfo = '';
      
      for (const element of reservationElements) {
        const idElement = element.querySelector('strong');
        if (idElement && idElement.textContent === reservationId) {
          const totalElement = element.querySelector('.fa-peso-sign').parentElement;
          const dateElement = element.querySelector('.fa-calendar').parentElement;
          
          reservationInfo = `
            <strong>${reservationId}</strong><br>
            ${dateElement.textContent}<br>
            ${totalElement.textContent}
          `;
          break;
        }
      }
      
      // Update modal content
      document.getElementById('modalReservationInfo').innerHTML = reservationInfo;
      
      // Show modal
      document.getElementById('cancelModal').classList.add('show');
    }

    function closeCancelModal() {
      document.getElementById('cancelModal').classList.remove('show');
      currentReservationId = null;
    }

    async function confirmCancellation() {
      if (!currentReservationId || isCancelling) return;
      
      // Set flag to prevent duplicate calls
      isCancelling = true;
      
      const confirmBtn = document.getElementById('confirmCancelBtn');
      const originalText = confirmBtn.innerHTML;
      
      // Show loading state and disable button immediately
      confirmBtn.disabled = true;
      confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
      
      try {
        const response = await fetch(`/customer/reservations/${currentReservationId}/cancel`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        
        // Check if response is ok
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check if response has JSON content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('Server returned non-JSON response');
        }
        
        const data = await response.json();
        
        if (data.success) {
          // Close modal first
          closeCancelModal();
          
          // Show success state briefly
          confirmBtn.innerHTML = '<i class="fas fa-check"></i> Cancelled!';
          confirmBtn.style.background = 'linear-gradient(135deg, #38a169 0%, #2f855a 100%)';
          
          // Update the UI immediately instead of reloading
          const reservationCards = document.querySelectorAll(`[data-reservation-id="${currentReservationId}"]`);
          let cardRemoved = false;
          reservationCards.forEach(card => {
            card.remove();
            cardRemoved = true;
          });
          
          // If no cards were removed, fallback to page reload
          if (!cardRemoved) {
            setTimeout(() => {
              location.reload();
            }, 1000);
            return;
          }
          
          // Optional: Show success message
          const successMessage = document.createElement('div');
          successMessage.className = 'alert alert-success';
          successMessage.innerHTML = '<i class="fas fa-check-circle"></i> Reservation cancelled successfully!';
          successMessage.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; padding: 12px 20px; border-radius: 8px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724;';
          document.body.appendChild(successMessage);
          
          setTimeout(() => {
            successMessage.remove();
          }, 3000);
          
        } else {
          throw new Error(data.message || 'Failed to cancel reservation.');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while cancelling the reservation. Please try again.');
        
        // Reset button
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
      } finally {
        // Always reset the flag
        isCancelling = false;
      }
    }
    
    function switchTab(tabName) {
      // Remove active class from all tabs and content
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
      
      // Add active class to clicked tab and corresponding content
      document.getElementById(tabName + '-tab').classList.add('active');
      document.getElementById(tabName + '-content').classList.add('active');
    }

    function toggleReservationItems(reservationId) {
      const toggle = document.getElementById('toggle-' + reservationId);
      const itemsList = document.getElementById('items-' + reservationId);
      
      if (toggle && itemsList) {
        const isExpanded = toggle.classList.contains('expanded');
        
        if (isExpanded) {
          // Collapse
          toggle.classList.remove('expanded');
          itemsList.classList.remove('expanded');
        } else {
          // Expand
          toggle.classList.add('expanded');
          itemsList.classList.add('expanded');
        }
      }
    }

    // Image Preview Functions
    function showImagePreview(src, alt) {
      const modal = document.getElementById('imagePreviewModal');
      const img = document.getElementById('previewImage');
      img.src = src;
      img.alt = alt;
      modal.classList.add('show');
    }

    function closeImagePreview() {
      const modal = document.getElementById('imagePreviewModal');
      modal.classList.remove('show');
    }

    // Modal event listeners
    document.addEventListener('DOMContentLoaded', function() {
      // Cancel modal functionality
      const cancelModal = document.getElementById('cancelModal');
      const imageModal = document.getElementById('imagePreviewModal');
      const confirmBtn = document.getElementById('confirmCancelBtn');
      
      // Close cancel modal on backdrop click
      cancelModal.addEventListener('click', function(e) {
        if (e.target === cancelModal) {
          closeCancelModal();
        }
      });
      
      // Close image modal on backdrop click
      imageModal.addEventListener('click', function(e) {
        if (e.target === imageModal) {
          closeImagePreview();
        }
      });
      
      // Close modals on Escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          if (cancelModal.classList.contains('show')) {
            closeCancelModal();
          }
          if (imageModal.classList.contains('show')) {
            closeImagePreview();
          }
        }
      });
      
      // Confirm cancellation - remove any existing listeners first
      confirmBtn.removeEventListener('click', confirmCancellation);
      confirmBtn.addEventListener('click', confirmCancellation);
    });

    // User menu dropdown functionality (matching portal behavior)
    document.addEventListener('DOMContentLoaded', function() {
      const svUser = document.querySelector('.sv-user');
      if (svUser) {
        const btn = svUser.querySelector('.sv-user-btn');
        const menu = svUser.querySelector('.sv-user-menu');
        
        if (btn && menu) {
          btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('open');
            const isOpen = menu.classList.contains('open');
            btn.setAttribute('aria-expanded', isOpen);
          });

          // Close dropdown when clicking outside
          document.addEventListener('click', function() {
            menu.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
          });

          // Prevent menu from closing when clicking inside
          menu.addEventListener('click', function(e) {
            e.stopPropagation();
          });
        }
      }
    });

  </script>
  <script>
    // Page entrance animations using anime.js (if available)
    (function(){
      if (typeof anime === 'undefined') return; if (window.__reservationsAnimated) return; window.__reservationsAnimated = true;
      function run(){
        // Header (mobile only visible)
        anime({ targets: '.dashboard-header.mobile-only', opacity:[0,1], translateY:[-12,0], duration:650, easing:'easeOutQuad', delay:50 });
        // Stats cards stagger
        anime({ targets: '.reservation-stat-card', opacity:[0,1], translateY:[16,0], duration:640, easing:'easeOutQuad', delay: anime.stagger(90, {start:120}) });
        // Tabs container
        anime({ targets: '.tab-navigation', opacity:[0,1], translateY:[10,0], duration:560, easing:'easeOutQuad', delay:420 });
        // Reservation items (first visible tab only at first)
        anime({ targets: '#pending-content .reservation-item, #pending-content .empty-state', opacity:[0,1], scale:[.96,1], duration:520, easing:'easeOutQuad', delay: anime.stagger(70,{start:520}) });
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run); else run();
    })();
  </script>
</body>
</html>





