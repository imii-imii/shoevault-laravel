<script>
(function(){
  const THRESHOLD = 991; // px: block at widths typical of phones/tablets in portrait
  const ID = 'sv-mobile-blocker';
  function ensureAnime(cb){
    if (window.anime) return cb();
    let existing = document.getElementById('animejs-cdn');
    if (existing){ existing.addEventListener('load', cb, { once: true }); return; }
    const s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js';
    s.id = 'animejs-cdn';
    s.defer = true;
    s.onload = cb;
    document.head.appendChild(s);
  }
  function injectStyles(){
    if (document.getElementById(ID+'-style')) return;
    const st = document.createElement('style');
    st.id = ID+'-style';
    st.textContent = `
      .sv-blocker-overlay{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:linear-gradient(180deg,rgba(15,23,42,.88),rgba(15,23,42,.92));backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); z-index: 2147483000;}
      .sv-blocker-overlay.show{display:flex;}
      .sv-blocker-card{width:min(520px,92vw);background:#0b1220;color:#e5e7eb;border:1px solid rgba(148,163,184,.2);border-radius:16px;padding:22px;box-shadow:0 30px 80px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.04)}
      .sv-blocker-header{display:flex;align-items:center;gap:12px;margin-bottom:8px}
      .sv-blocker-icon{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#3b82f6,#1d4ed8);box-shadow:0 8px 22px rgba(29,78,216,.35)}
      .sv-blocker-title{font-size:1.05rem;font-weight:800;color:#fff}
      .sv-blocker-text{color:#94a3b8;font-size:.95rem;line-height:1.5}
      .sv-blocker-hint{margin-top:12px;display:flex;align-items:center;gap:8px;color:#93c5fd;font-weight:700;font-size:.85rem}
      .sv-logo{display:flex;align-items:center;gap:10px;margin-bottom:6px;color:#c7d2fe;font-weight:800;letter-spacing:.4px}
      @media (prefers-reduced-motion: reduce){ .sv-blocker-overlay, .sv-blocker-card{ transition:none !important; animation:none !important; } }
    `;
    document.head.appendChild(st);
  }
  function buildOverlay(){
    if (document.getElementById(ID)) return document.getElementById(ID);
    injectStyles();
    const overlay = document.createElement('div');
    overlay.id = ID;
    overlay.className = 'sv-blocker-overlay';
    overlay.setAttribute('aria-hidden','true');
    overlay.innerHTML = `
      <div class="sv-blocker-card" role="dialog" aria-modal="true" aria-labelledby="svb-title">
        <div class="sv-logo"><i class="fas fa-shoe-prints"></i> ShoeVault</div>
        <div class="sv-blocker-header">
          <div class="sv-blocker-icon"><i class="fas fa-mobile-alt" style="color:#fff;"></i></div>
          <div class="sv-blocker-title" id="svb-title">Not available on mobile</div>
        </div>
        <div class="sv-blocker-text">This interface is designed for desktop screens. Please switch to a desktop or widen your browser window to continue.</div>
        <div class="sv-blocker-hint"><i class="fas fa-desktop"></i> Tip: Use a device with a width above 992px.</div>
      </div>`;
    document.body.appendChild(overlay);
    return overlay;
  }
  function show(){
    const overlay = buildOverlay();
    if (!overlay) return;
    if (!overlay.classList.contains('show')){
      overlay.style.opacity = 0;
      overlay.classList.add('show');
      overlay.setAttribute('aria-hidden','false');
      ensureAnime(function(){
        try{
          anime.set('.sv-blocker-card', { opacity: 0, translateY: 16, scale: .98 });
          anime({ targets: overlay, opacity: [0,1], duration: 220, easing: 'linear' });
          anime({ targets: '.sv-blocker-card', opacity: [0,1], translateY: [16,0], scale: [.98,1], duration: 420, easing: 'easeOutCubic' });
        }catch(e){ overlay.style.opacity = 1; }
      });
    }
    // Prevent page scroll/interactions
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';
  }
  function hide(){
    const overlay = document.getElementById(ID);
    if (!overlay) return;
    if (overlay.classList.contains('show')){
      ensureAnime(function(){
        try{
          anime({ targets: overlay, opacity: [1,0], duration: 200, easing: 'easeInCubic', complete: ()=>{ overlay.classList.remove('show'); overlay.style.opacity=''; overlay.setAttribute('aria-hidden','true'); } });
        }catch(e){ overlay.classList.remove('show'); overlay.style.opacity=''; overlay.setAttribute('aria-hidden','true'); }
      });
    }
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';
  }
  function isMobile(){ return window.innerWidth <= THRESHOLD; }
  function update(){ isMobile() ? show() : hide(); }
  let resizeTimer;
  window.addEventListener('resize', function(){ clearTimeout(resizeTimer); resizeTimer = setTimeout(update, 120); }, { passive: true });
  window.addEventListener('orientationchange', function(){ setTimeout(update, 150); }, { passive: true });
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', update, { once: true });
  } else {
    update();
  }
})();
</script>