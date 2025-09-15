(function(){
  const slider = document.querySelector('.slider');
  const slides = document.querySelectorAll('.slide');
  const dots = document.querySelectorAll('.dot');
  if (!slides.length) return;

  let idx = 0;
  let timer = null;
  const hasMultiple = slides.length > 1;

  function normalizeIndex(i){
    if (!hasMultiple) return 0;
    const n = slides.length;
    return (i % n + n) % n;
  }

  function show(i){
    idx = normalizeIndex(i);
    slides.forEach((s,k)=>s.classList.toggle('active', k===idx));
    if (dots && dots.length) {
      dots.forEach((d,k)=>d.classList.toggle('active', k===idx));
    }
  }

  function next(){ show(idx + 1); }
  function prev(){ show(idx - 1); }

  function start(){ if (!timer && hasMultiple) { timer = setInterval(next, 5000); } }
  function stop(){ if (timer) { clearInterval(timer); timer = null; } }

  // Dots click
  if (dots && dots.length) {
    dots.forEach((d,i)=> d.addEventListener('click', ()=>{ stop(); show(i); start(); }));
  }

  // Pause on hover and enable swipe on touch
  if (slider) {
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);

    let touchStartX = 0;
    slider.addEventListener('touchstart', e => {
      if (e.touches && e.touches[0]) touchStartX = e.touches[0].clientX;
    }, {passive:true});
    slider.addEventListener('touchend', e => {
      const endX = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0].clientX : 0;
      const dx = endX - touchStartX;
      if (Math.abs(dx) > 40) { stop(); (dx < 0 ? next() : prev()); start(); }
    }, {passive:true});
  }

  // Pause when tab hidden
  document.addEventListener('visibilitychange', ()=>{ document.hidden ? stop() : start(); });

  // Keyboard navigation
  document.addEventListener('keydown', e => {
    if (e.key === 'ArrowRight') { stop(); next(); start(); }
    else if (e.key === 'ArrowLeft') { stop(); prev(); start(); }
  });

  // Init
  show(0);
  start();
})();