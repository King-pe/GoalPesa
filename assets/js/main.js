(function(){
  const slides = document.querySelectorAll('.slide');
  const dots = document.querySelectorAll('.dot');
  if (!slides.length) return;
  let idx = 0;
  function show(i){
    slides.forEach((s,k)=>s.classList.toggle('active', k===i));
    dots.forEach((d,k)=>d.classList.toggle('active', k===i));
  }
  function next(){ idx = (idx+1)%slides.length; show(idx); }
  dots.forEach((d,i)=> d.addEventListener('click', ()=>{ idx=i; show(idx); }));
  setInterval(next, 5000);
})();