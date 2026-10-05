const $=(s,p=document)=>p.querySelector(s), $$=(s,p=document)=>[...p.querySelectorAll(s)];
if('scrollRestoration'in history)history.scrollRestoration='manual';
if(location.hash)history.replaceState(null,'',location.pathname+location.search);
const resetToTop=()=>scrollTo({top:0,left:0,behavior:'instant'});resetToTop();addEventListener('pageshow',resetToTop);
const guest=$('[data-guest]').textContent.trim();$('#rsvpName').value=guest.includes('&')||guest==='Tamu Undangan'?'':guest;

const gate=$('#gate'), card=$('#inviteCard'); let opened=false;
let cardFrame;
card.addEventListener('pointermove',e=>{if(innerWidth<700||opened)return;const x=e.clientX,y=e.clientY;cancelAnimationFrame(cardFrame);cardFrame=requestAnimationFrame(()=>{const r=card.getBoundingClientRect(),rx=(x-r.left)/r.width-.5,ry=(y-r.top)/r.height-.5;card.style.animation='none';card.style.transform=`rotateY(${rx*12}deg) rotateX(${-ry*10}deg) translateZ(15px)`})});
card.addEventListener('pointerleave',()=>{card.style.animation='';card.style.transform=''});
$('#openInvitation').addEventListener('click',()=>{if(opened)return;opened=true;resetToTop();card.style.animation='';card.style.transform='';requestAnimationFrame(()=>{resetToTop();gate.classList.add('opening');document.body.classList.remove('locked')});setTimeout(()=>{resetToTop();gate.classList.add('opened');$('#home').focus?.();sessionStorage.setItem('invitationOpened','1')},1850)});

const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');observer.unobserve(e.target)}}),{threshold:.14}); $$('.reveal').forEach(el=>observer.observe(el));
const heroBg=$('.hero-bg');let scrollFrame;
addEventListener('scroll',()=>{if(scrollFrame)return;scrollFrame=requestAnimationFrame(()=>{const y=scrollY;if(heroBg&&y<innerHeight)heroBg.style.transform=`scale(1.06) translate3d(0,${y*.13}px,0)`;scrollFrame=null})},{passive:true});

const wedding=new Date('2027-01-18T08:00:00+07:00');
function tick(){let d=Math.max(0,wedding-Date.now());const vals=[Math.floor(d/864e5),Math.floor(d/36e5)%24,Math.floor(d/6e4)%60,Math.floor(d/1000)%60];['days','hours','minutes','seconds'].forEach((id,i)=>$('#'+id).textContent=String(vals[i]).padStart(i?2:3,'0'))} tick();setInterval(tick,1000);

const canvas=$('#particles'),ctx=canvas.getContext('2d');let pts=[];
function resize(){canvas.width=innerWidth*devicePixelRatio;canvas.height=innerHeight*devicePixelRatio;ctx.setTransform(devicePixelRatio,0,0,devicePixelRatio,0,0);pts=Array.from({length:innerWidth<700?22:48},()=>({x:Math.random()*innerWidth,y:Math.random()*innerHeight,r:Math.random()*1.5+.3,v:Math.random()*.22+.06,a:Math.random()*.6+.15}))}resize();addEventListener('resize',resize);
function particles(){ctx.clearRect(0,0,innerWidth,innerHeight);pts.forEach(p=>{p.y-=p.v;if(p.y<0){p.y=innerHeight;p.x=Math.random()*innerWidth}ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,7);ctx.fillStyle=`rgba(238,208,144,${p.a})`;ctx.shadowBlur=8;ctx.shadowColor='#d6b66f';ctx.fill()});requestAnimationFrame(particles)}particles();

const toast=msg=>{const el=$('#toast');el.textContent=msg;el.classList.add('show');clearTimeout(el.t);el.t=setTimeout(()=>el.classList.remove('show'),2600)};
$$('[data-copy]').forEach(b=>b.onclick=async()=>{try{await navigator.clipboard.writeText(b.dataset.copy);toast('Nomor rekening berhasil disalin')}catch{toast('Nomor rekening: '+b.dataset.copy)}});
$('#saveQr').onclick=()=>toast('QR check-in tersimpan untuk '+guest);
$('#calendarBtn').onclick=()=>{const event=['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//Mr & Mrs Wedding//ID','BEGIN:VEVENT','UID:wedding-20270118@example.local','DTSTAMP:20260926T000000Z','DTSTART:20270118T010000Z','DTEND:20270118T070000Z','SUMMARY:The Wedding of Mr & Mrs','LOCATION:Gedung Arsip Nasional, Jl. Gajah Mada No. 111, Jakarta Barat','DESCRIPTION:Akad nikah dan resepsi pernikahan Mr & Mrs.','END:VEVENT','END:VCALENDAR'].join('\r\n');const url=URL.createObjectURL(new Blob([event],{type:'text/calendar;charset=utf-8'})),link=document.createElement('a');link.href=url;link.download='mr-mrs-wedding.ics';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);toast('Tanggal pernikahan ditambahkan ke kalender')};

const stored=JSON.parse(localStorage.getItem('weddingWishes')||'[]');stored.forEach(addWish);updateCount();
function addWish(w){const q=document.createElement('blockquote');q.innerHTML=`<p>“${escapeHtml(w.message)}”</p><footer>— ${escapeHtml(w.name)}</footer>`;$('#wishList').prepend(q)}
function updateCount(){$('#wishCount').textContent=`${$$('#wishList blockquote').length} ucapan`}
function escapeHtml(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML}
$('#rsvpForm').addEventListener('submit',e=>{e.preventDefault();const data=Object.fromEntries(new FormData(e.target));const list=JSON.parse(localStorage.getItem('weddingWishes')||'[]');list.unshift({name:data.name,message:data.message});localStorage.setItem('weddingWishes',JSON.stringify(list.slice(0,20)));addWish(data);updateCount();$('#formStatus').textContent='Terima kasih. Konfirmasi Anda telah kami simpan ♥';toast('Konfirmasi kehadiran terkirim');e.target.reset()});

const dialog=$('#lightbox'),lightboxImage=dialog.querySelector('.lightbox-image');let lightboxTimer;const closeLightbox=()=>{if(!dialog.open||dialog.classList.contains('closing'))return;dialog.classList.add('closing');clearTimeout(lightboxTimer);lightboxTimer=setTimeout(()=>{dialog.close();dialog.classList.remove('closing')},980)};$$('.photo').forEach(p=>p.onclick=()=>{const style=getComputedStyle(p);clearTimeout(lightboxTimer);dialog.classList.remove('closing');dialog.classList.add('opening');lightboxImage.style.setProperty('--lightbox-photo',style.backgroundImage);lightboxImage.style.setProperty('--lightbox-position',style.backgroundPosition);dialog.showModal();requestAnimationFrame(()=>requestAnimationFrame(()=>dialog.classList.remove('opening')))});dialog.querySelector('button').onclick=closeLightbox;dialog.onclick=e=>{if(e.target===dialog)closeLightbox()};dialog.addEventListener('cancel',e=>{e.preventDefault();closeLightbox()});

const memoireInstagram=$('#memoireInstagram');memoireInstagram.addEventListener('click',e=>{const mobile=/Android|iPhone|iPad|iPod/i.test(navigator.userAgent);if(!mobile)return;e.preventDefault();const webUrl=memoireInstagram.href;let fallback;const stopFallback=()=>{if(document.hidden){clearTimeout(fallback);document.removeEventListener('visibilitychange',stopFallback)}};document.addEventListener('visibilitychange',stopFallback);location.href='instagram://user?username=memoire.___';fallback=setTimeout(()=>{document.removeEventListener('visibilitychange',stopFallback);location.href=webUrl},1200)});

let audioCtx,osc,gain,musicOn=false;$('#musicToggle').onclick=()=>{if(!musicOn){audioCtx=new(window.AudioContext||window.webkitAudioContext)();gain=audioCtx.createGain();gain.gain.value=.025;gain.connect(audioCtx.destination);osc=audioCtx.createOscillator();osc.type='sine';osc.frequency.value=174;osc.connect(gain);osc.start();musicOn=true;$('#musicToggle').innerHTML='Ⅱ <span>Ambience</span>';toast('Ambient tone dinyalakan')}else{gain.gain.exponentialRampToValueAtTime(.0001,audioCtx.currentTime+.4);setTimeout(()=>audioCtx.close(),500);musicOn=false;$('#musicToggle').innerHTML='♫ <span>Ambience</span>'}};
$$('.tilt').forEach(el=>{let frame;el.addEventListener('pointermove',e=>{if(innerWidth<800)return;const x=e.clientX,y=e.clientY;cancelAnimationFrame(frame);frame=requestAnimationFrame(()=>{const r=el.getBoundingClientRect();el.style.transform=`perspective(900px) rotateX(${-(y-r.top-r.height/2)/35}deg) rotateY(${(x-r.left-r.width/2)/35}deg)`})});el.addEventListener('pointerleave',()=>{cancelAnimationFrame(frame);el.style.transform=''})});
