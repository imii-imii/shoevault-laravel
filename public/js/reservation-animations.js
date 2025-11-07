/**
 * Anime.js Animation Controller for Reservation Home Page
 * Handles floating shoes animations, welcome section entrance, and interactive effects
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // WELCOME INFO ENTRANCE ANIMATION
    // ============================================
    
    // Animate welcome header with stagger effect
    anime({
        targets: '.welcome-header h1, .welcome-header h2',
        translateY: [60, 0],
        opacity: [0, 1],
        easing: 'easeOutExpo',
        duration: 1200,
        delay: anime.stagger(150, {start: 300})
    });
    
    // Animate welcome description
    anime({
        targets: '.welcome-description p',
        translateY: [40, 0],
        opacity: [0, 1],
        easing: 'easeOutExpo',
        duration: 1000,
        delay: 600
    });
    
    // Animate feature items with stagger
    anime({
        targets: '.feature-item',
        translateX: [-40, 0],
        opacity: [0, 1],
        easing: 'easeOutExpo',
        duration: 800,
        delay: anime.stagger(100, {start: 800})
    });
    
    // Animate CTA buttons with bounce
    anime({
        targets: '.welcome-actions a',
        scale: [0.8, 1],
        opacity: [0, 1],
        easing: 'easeOutElastic(1, .6)',
        duration: 1500,
        delay: anime.stagger(150, {start: 1200})
    });
    
    
    
    // ============================================
    // INTERACTIVE GLOW EFFECTS
    // ============================================
    
    // Enhanced hover effect for floating shoes
    
    
    // ============================================
    // SCROLL-TRIGGERED ANIMATIONS
    // ============================================
    
    // Animate feature icons on hover
    const featureItems = document.querySelectorAll('.feature-item');
    featureItems.forEach(item => {
        const icon = item.querySelector('i');
        
        item.addEventListener('mouseenter', function() {
            anime({
                targets: icon,
                scale: [1, 1.3, 1.15],
                rotate: [0, 360],
                duration: 600,
                easing: 'easeOutElastic(1, .5)'
            });
        });
    });
    
    // CTA button hover animations
    const ctaButtons = document.querySelectorAll('.cta-primary, .cta-secondary');
    ctaButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            anime({
                targets: this,
                translateY: -4,
                duration: 300,
                easing: 'easeOutQuad'
            });
        });
        
        btn.addEventListener('mouseleave', function() {
            anime({
                targets: this,
                translateY: 0,
                duration: 300,
                easing: 'easeOutQuad'
            });
        });
    });
    
    // ============================================
    // WINDOW SHOPPING VERTICAL SHOWCASE (Ad-like)
    // ============================================
    
    const showcaseContainer = document.querySelector('.showcase-container');
    const floatingShoes = document.querySelectorAll('.floating-shoe');
    
    if (showcaseContainer && floatingShoes.length > 0) {
        // Create spotlight and indicators
        const adSpotlight = document.createElement('div');
        adSpotlight.className = 'ad-spotlight';
        
        const indicators = document.createElement('div');
        indicators.className = 'showcase-indicators';
        
    // Build track
        const scrollTrack = document.createElement('div');
        scrollTrack.className = 'shoe-scroll-track';
    const baseShoes = Array.from(floatingShoes);
    // Append only one set; we'll ping-pong up/down instead of looping duplicates
    baseShoes.forEach(shoe => scrollTrack.appendChild(shoe.cloneNode(true)));
        
        // Clear and mount
        showcaseContainer.innerHTML = '';
        showcaseContainer.appendChild(adSpotlight);
        showcaseContainer.appendChild(scrollTrack);
        // Indicators for base set
        baseShoes.forEach((_, i) => {
            const dot = document.createElement('div');
            dot.className = 'indicator-dot' + (i === 0 ? ' active' : '');
            dot.dataset.idx = String(i);
            indicators.appendChild(dot);
        });
        showcaseContainer.appendChild(indicators);
        
        const allShoes = scrollTrack.querySelectorAll('.floating-shoe');
    let currentIndex = 0;
    let direction = 1; // 1 = downwards (next index), -1 = upwards (previous index)
        let isAnimating = false;
        let autoPlayInterval = null;
        
        function updateIndicators(idx){
            const dots = indicators.querySelectorAll('.indicator-dot');
            dots.forEach((d,i)=> d.classList.toggle('active', i === (idx % baseShoes.length)));
        }
        
        function getItemHeight() {
            const first = allShoes[0];
            if (!first) return 0;
            const styles = getComputedStyle(scrollTrack);
            const gap = parseFloat(styles.rowGap || styles.gap || '80');
            return first.offsetHeight + (isNaN(gap) ? 80 : gap);
        }
        let itemHeight = 0;

        function getCurrentTranslateY() {
            const val = anime.get(scrollTrack, 'translateY', 'px');
            return typeof val === 'number' ? val : parseFloat(val || '0') || 0;
        }

        function desiredTranslateToCenter(index) {
            const containerRect = showcaseContainer.getBoundingClientRect();
            const target = allShoes[index];
            const targetRect = target.getBoundingClientRect();
            const currentTY = getCurrentTranslateY();
            const containerCenter = containerRect.top + containerRect.height / 2;
            const targetCenter = targetRect.top + targetRect.height / 2;
            const offsetToCenter = containerCenter - targetCenter;
            return currentTY + offsetToCenter;
        }
        
        // Initial entrance and center the first shoe to the vertical middle
        requestAnimationFrame(()=>{
            itemHeight = getItemHeight();
            // center index 0 to container middle (set absolute transform)
            const initialTY = desiredTranslateToCenter(0);
            scrollTrack.style.transform = `translateY(${initialTY}px)`;
            
            anime({
                targets: allShoes,
                opacity: [0, 0.4],
                translateX: [100, 0],
                rotate: [-45, -25],
                scale: [0.5, 0.7],
                delay: anime.stagger(80, {start: 200}),
                duration: 900,
                easing: 'easeOutElastic(1, .6)',
                complete(){
                    const first = allShoes[currentIndex];
                    first.classList.add('active');
                    const label = first.querySelector('.shoe-label');
                    if (label){ anime({targets: label, translateY:[20,0], opacity:[0,1], duration:500, easing:'easeOutQuad'}); }
                    startAuto();
                }
            });
        });
        
        function showNext(){
            if (isAnimating) return; isAnimating = true;
            const prev = allShoes[currentIndex];
            // Compute next index in ping-pong
            let nextIndex = currentIndex + direction;
            if (nextIndex >= allShoes.length || nextIndex < 0) {
                direction *= -1; // reverse
                nextIndex = currentIndex + direction;
            }
            const next = allShoes[nextIndex];
            const tl = anime.timeline({ easing:'easeInOutQuad', complete(){
                isAnimating = false; currentIndex = nextIndex; 
                updateIndicators(currentIndex);
                // schedule next after dwell
                scheduleNext();
            }});
            // Shrink current
            tl.add({ targets: prev, scale:[1.15,0.6], opacity:[1,0.3], duration:550, easing:'easeInBack', complete(){ prev.classList.remove('active'); prev.classList.add('previous'); }}, 0);
            // Motion blur start
            tl.add({ targets: scrollTrack, filter:['blur(0px)','blur(3px)'], duration:220, easing:'linear' }, 150);
            // Scroll up
            tl.add({
                targets: scrollTrack,
                translateY: desiredTranslateToCenter(nextIndex),
                duration: 780,
                easing: 'easeInOutCubic'
            }, 260);
            // Zoom next
            tl.add({ targets: next, scale:[0.7,1.15], opacity:[0.4,1], duration:650, easing:'easeOutElastic(1,.5)', begin(){ next.classList.add('active'); } }, 440);
            // Label reveal
            const nextLabel = next.querySelector('.shoe-label');
            if (nextLabel){ tl.add({ targets: nextLabel, translateY:[20,0], opacity:[0,1], duration:480, easing:'easeOutQuad' }, 520); }
            // Spotlight pop
            tl.add({ targets: adSpotlight, scale:[1.0,1.06,1.0], opacity:[0.82,0.9,0.85], duration:520, easing:'easeInOutSine' }, 520);
            // Clear blur
            tl.add({ targets: scrollTrack, filter:['blur(3px)','blur(0px)'], duration:200, easing:'linear' }, 900);
        }
        const DWELL_MS = 2000; // highlighted shoe stays centered ~2s
        let autoTimer = null; let autoRunning = false;
        function scheduleNext(){ if(!autoRunning) return; autoTimer = setTimeout(()=>{ showNext(); }, DWELL_MS); }
    function startAuto(){ stopAuto(); autoRunning = true; scheduleNext(); }
        function stopAuto(){ autoRunning = false; if (autoTimer){ clearTimeout(autoTimer); autoTimer=null; } }
        
        // Continuous glow pulse
        anime({ targets: allShoes, filter:[
            'drop-shadow(0 15px 35px rgba(0,0,0,0.15)) drop-shadow(0 0 40px rgba(42,106,255,0.45)) drop-shadow(0 0 90px rgba(42,106,255,0.28))',
            'drop-shadow(0 15px 35px rgba(0,0,0,0.15)) drop-shadow(0 0 60px rgba(42,106,255,0.75)) drop-shadow(0 0 140px rgba(42,106,255,0.5))'
        ], direction:'alternate', duration:2200, easing:'easeInOutSine', loop:true });
        
        // (Removed) Hover pause/resume for floating shoes per request
        // showcaseContainer.addEventListener('mouseenter', stopAuto);
        // showcaseContainer.addEventListener('mouseleave', ()=>{ if(!isAnimating) startAuto(); });
        
        // Indicator click
        indicators.addEventListener('click', (e)=>{
            const t = e.target; if (!(t instanceof Element)) return;
            if (!t.classList.contains('indicator-dot')) return;
            const idx = parseInt(t.getAttribute('data-idx')||'0',10);
            if (isNaN(idx)) return;
            stopAuto();
            // Compute steps from base set alignment
            let steps = idx - (currentIndex % baseShoes.length);
            if (steps < 0) steps += baseShoes.length;
            if (steps === 0) { startAuto(); return; }
            let count = 0; const jump = setInterval(()=>{ showNext(); count++; if(count>=steps){ clearInterval(jump); setTimeout(startAuto, 800);} }, 350);
        });
        
        // (Removed) 3D tilt hover interaction for active shoe per request
        // showcaseContainer.addEventListener('mousemove', ...);
        // showcaseContainer.addEventListener('mouseleave', ...);

        // Recalculate on resize to keep centering accurate
        window.addEventListener('resize', () => {
            itemHeight = getItemHeight();
            const ty = desiredTranslateToCenter(currentIndex);
            scrollTrack.style.transform = `translateY(${ty}px)`;
        });
    }
    
    // ============================================
    // PARALLAX EFFECT FOR WELCOME INFO (optional: left as-is)
    // ============================================
});