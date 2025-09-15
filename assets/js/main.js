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

// Dropdown
(() => {
  const btn = document.querySelector('.hamburger');
  const menu = document.querySelector('.dropdown-menu');
  if (!btn || !menu) return;
  btn.addEventListener('click', () => {
    const isOpen = menu.style.display === 'block';
    menu.style.display = isOpen ? 'none' : 'block';
  });
  document.addEventListener('click', (e) => {
    if (!menu.contains(e.target) && !btn.contains(e.target)) {
      menu.style.display = 'none';
    }
  });
})();