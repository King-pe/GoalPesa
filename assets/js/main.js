(() => {
  const slider = document.querySelector('.slider');
  if (!slider) return;
  const slides = slider.querySelector('.slides');
  const items = Array.from(slider.querySelectorAll('.slide'));
  const dots = Array.from(slider.querySelectorAll('.dot'));
  let idx = 0;
  function go(i){
    idx = (i + items.length) % items.length;
    slides.style.transform = `translateX(-${idx*100}%)`;
    dots.forEach((d,di)=>d.classList.toggle('active', di===idx));
  }
  setInterval(()=>go(idx+1), 4000);
  dots.forEach((d,i)=>d.addEventListener('click', ()=>go(i)));
})();
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