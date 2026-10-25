/* Landing page interactions only; no backend requests. */
(() => {
'use strict';
const toggle=document.querySelector('.nav-toggle');
const nav=document.querySelector('#primary-nav');
if(toggle&&nav){
 const close=()=>{toggle.setAttribute('aria-expanded','false');toggle.setAttribute('aria-label','باز کردن منو');nav.classList.remove('is-open');};
 toggle.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')!=='true';toggle.setAttribute('aria-expanded',String(open));toggle.setAttribute('aria-label',open?'بستن منو':'باز کردن منو');nav.classList.toggle('is-open',open);});
 nav.querySelectorAll('a').forEach(link=>link.addEventListener('click',close));
 document.addEventListener('keydown',event=>{if(event.key==='Escape'){close();toggle.focus();}});
}
// Interactive visual demo only — these actions do not submit real orders.
const buttons=document.querySelectorAll('.add-item');
const countNode=document.querySelector('.cart-count');
const totalNode=document.querySelector('.cart-total');
let count=0,total=0;
const prices=[125000,150000];
const faNumber=value=>new Intl.NumberFormat('fa-IR').format(value);
buttons.forEach((button,index)=>button.addEventListener('click',()=>{
 count++;total+=prices[index]||0;
 if(countNode)countNode.textContent=faNumber(count);
 if(totalNode)totalNode.textContent=faNumber(total)+' تومان';
 button.textContent='✓';
 window.setTimeout(()=>{button.textContent='+';},650);
}));
})();