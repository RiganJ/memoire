const $=(s,p=document)=>p.querySelector(s), $$=(s,p=document)=>[...p.querySelectorAll(s)];
const body=document.body, opening=$('#opening'), musicButton=$('#musicToggle');
let audioContext=null, musicTimer=null, musicPlaying=false, filmWasPlaying=false;

addEventListener('load',()=>setTimeout(()=>$('#loader').classList.add('hide'),650));

function playNote(ctx,freq,start,duration=.8,volume=.025){
  const oscillator=ctx.createOscillator(), gain=ctx.createGain();
  oscillator.type='sine'; oscillator.frequency.value=freq;
  gain.gain.setValueAtTime(0,start);gain.gain.linearRampToValueAtTime(volume,start+.18);gain.gain.exponentialRampToValueAtTime(.0001,start+duration);
  oscillator.connect(gain).connect(ctx.destination);oscillator.start(start);oscillator.stop(start+duration+.05);
}
function startMusic(){
  audioContext ||= new (window.AudioContext||window.webkitAudioContext)(); audioContext.resume(); musicPlaying=true;musicButton.classList.add('playing');musicButton.setAttribute('aria-label','Pause music');
  const progression=[[261.63,329.63,392],[220,261.63,329.63],[174.61,220,261.63],[196,246.94,293.66]];let step=0;
  const phrase=()=>{const now=audioContext.currentTime+.05, chord=progression[step++%progression.length];chord.forEach((note,i)=>playNote(audioContext,note,now+i*.55,2.25,.018));};phrase();musicTimer=setInterval(phrase,2500);
}
function stopMusic(){musicPlaying=false;musicButton.classList.remove('playing');musicButton.setAttribute('aria-label','Play music');clearInterval(musicTimer);musicTimer=null;if(audioContext)audioContext.suspend();}
$('#openInvitation').addEventListener('click',()=>{opening.classList.add('opened');body.classList.remove('locked');body.classList.add('entered');startMusic();setTimeout(()=>$('#home').scrollIntoView(),250);});
musicButton.addEventListener('click',()=>musicPlaying?stopMusic():startMusic());

const revealObserver=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('visible');revealObserver.unobserve(entry.target)}}),{threshold:.12,rootMargin:'0px 0px -30px'});
$$('.reveal').forEach(el=>revealObserver.observe(el));
const storyLine=$('.story-timeline');
if(storyLine)new IntersectionObserver(([entry],observer)=>{if(entry.isIntersecting){entry.target.classList.add('visible');observer.disconnect()}},{threshold:.03}).observe(storyLine);
$$('.story-toggle').forEach(button=>button.addEventListener('click',()=>{const content=button.closest('.story-content'),expanded=content.classList.toggle('expanded');button.setAttribute('aria-expanded',expanded);button.firstChild.textContent=expanded?'Show Less ':'Read Our Story '}));

const wedding=new Date('2027-01-18T08:00:00+07:00').getTime();
function countdown(){const diff=Math.max(0,wedding-Date.now());$('#days').textContent=String(Math.floor(diff/864e5)).padStart(3,'0');$('#hours').textContent=String(Math.floor(diff/36e5)%24).padStart(2,'0');$('#minutes').textContent=String(Math.floor(diff/6e4)%60).padStart(2,'0');$('#seconds').textContent=String(Math.floor(diff/1e3)%60).padStart(2,'0')};countdown();setInterval(countdown,1000);

const navSections=$$('[data-nav]'), mobileLinks=$$('.mobile-nav a');
const side=$('.side-nav');navSections.forEach((section,i)=>{const a=document.createElement('a');a.href='#'+section.id;a.dataset.target=section.dataset.nav;a.ariaLabel='Go to '+section.dataset.nav;if(i===0)a.className='active';side.append(a)});
const allNav=$$('.mobile-nav a, .side-nav a');
const sectionObserver=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){allNav.forEach(a=>a.classList.toggle('active',a.dataset.target===entry.target.dataset.nav))}}),{threshold:.45});navSections.forEach(s=>sectionObserver.observe(s));

let lightboxItems=[],lightIndex=0;
function imageData(elements){return elements.map(element=>{const img=$('img',element)||element;return{src:img.getAttribute('src'),alt:img.alt}})}
function showImage(index){lightIndex=(index+lightboxItems.length)%lightboxItems.length;$('#lightboxImage').src=lightboxItems[lightIndex].src;$('#lightboxImage').alt=lightboxItems[lightIndex].alt;$('#lightboxCount').textContent=`${String(lightIndex+1).padStart(2,'0')} / ${String(lightboxItems.length).padStart(2,'0')}`}
function openLightbox(items,index){lightboxItems=items;showImage(index);$('#lightbox').classList.add('show');body.classList.add('locked')}
const galleryButtons=$$('.gallery-item');galleryButtons.forEach((item,i)=>item.addEventListener('click',()=>openLightbox(imageData(galleryButtons),i)));
$$('.story-media').forEach(media=>{const photos=$$('.story-photo',media);photos.forEach((photo,i)=>photo.addEventListener('click',()=>openLightbox(imageData(photos),i)))});
$('#lightPrev').addEventListener('click',()=>showImage(lightIndex-1));$('#lightNext').addEventListener('click',()=>showImage(lightIndex+1));
let touchStartX=0;$('#lightbox').addEventListener('touchstart',e=>touchStartX=e.changedTouches[0].clientX,{passive:true});$('#lightbox').addEventListener('touchend',e=>{const distance=e.changedTouches[0].clientX-touchStartX;if(Math.abs(distance)>45)showImage(lightIndex+(distance<0?1:-1))},{passive:true});

function closeModal(modal){modal.classList.remove('show');body.classList.remove('locked');if(modal.id==='filmModal'&&filmWasPlaying)startMusic()}
$$('[data-close]').forEach(btn=>btn.addEventListener('click',()=>closeModal(btn.parentElement)));
[$('#lightbox'),$('#filmModal')].forEach(modal=>modal.addEventListener('click',e=>{if(e.target===modal)closeModal(modal)}));
$('#playFilm').addEventListener('click',()=>{filmWasPlaying=musicPlaying;if(musicPlaying)stopMusic();$('#filmModal').classList.add('show');body.classList.add('locked')});
addEventListener('keydown',e=>{if(e.key==='Escape')$$('.lightbox.show,.film-modal.show').forEach(closeModal);if($('#lightbox').classList.contains('show')&&e.key==='ArrowRight')showImage(lightIndex+1);if($('#lightbox').classList.contains('show')&&e.key==='ArrowLeft')showImage(lightIndex-1)});

function toast(message){const el=$('#toast');el.textContent=message;el.classList.add('show');clearTimeout(el.timer);el.timer=setTimeout(()=>el.classList.remove('show'),2200)}
$('#wishForm').addEventListener('submit',e=>{e.preventDefault();const name=$('#wishName').value.trim(),message=$('#wishMessage').value.trim();if(!name||!message)return;const item=document.createElement('article');item.className='wish visible';item.innerHTML=`<div>${name[0].toUpperCase()}</div><p><strong></strong><span></span><small>With love · just now</small></p>`;$('strong',item).textContent=name;$('span',item).textContent=message;$('#wishList').prepend(item);e.target.reset();toast('Your beautiful wish has been sent')});
$('#rsvpForm').addEventListener('submit',e=>{e.preventDefault();const valid=e.target.checkValidity(),status=$('#rsvpStatus');status.classList.add('show');if(!valid){status.textContent='Please complete your name, guest count, and attendance.';e.target.reportValidity();return}status.textContent='Thank you. Your attendance has been lovingly noted.';e.target.reset();toast('Attendance confirmed')});
$('#copyAccount').addEventListener('click',async()=>{try{await navigator.clipboard.writeText($('#accountNumber').textContent);toast('Account number copied')}catch{toast('Account: '+$('#accountNumber').textContent)}});

let ticking=false;addEventListener('scroll',()=>{if(!ticking){requestAnimationFrame(()=>{const y=scrollY;$$('.parallax').forEach((el,i)=>el.style.transform=`translate3d(0,${(y-el.closest('.section').offsetTop)*(.025+(i%2)*.012)}px,0)`);$('.scroll-glow').style.transform=`translate(-50%,-50%) translateY(${Math.sin(y/350)*55}px)`;ticking=false});ticking=true}},{passive:true});
if(matchMedia('(min-width:1050px)').matches){const cursor=$('#cursor');addEventListener('pointermove',e=>{cursor.style.left=e.clientX+'px';cursor.style.top=e.clientY+'px';if(Math.random()>.88){const star=document.createElement('i');star.className='fa-solid fa-star';Object.assign(star.style,{position:'fixed',zIndex:10003,left:e.clientX+'px',top:e.clientY+'px',color:'#d8b06a',fontSize:'7px',pointerEvents:'none',transition:'1s',opacity:.8});document.body.append(star);requestAnimationFrame(()=>{star.style.transform='translateY(-18px) scale(.2)';star.style.opacity=0});setTimeout(()=>star.remove(),1000)}});$$('a,button').forEach(el=>{el.addEventListener('mouseenter',()=>cursor.classList.add('hover'));el.addEventListener('mouseleave',()=>cursor.classList.remove('hover'))})}
