const $=(selector,parent=document)=>parent.querySelector(selector);
const $$=(selector,parent=document)=>[...parent.querySelectorAll(selector)];
const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
const mobile=matchMedia('(max-width: 760px)').matches;
const lowPower=mobile&&((navigator.deviceMemory||4)<=4||navigator.hardwareConcurrency<=4);
$$('section:not(#opening):not(#hero) img').forEach(image=>{image.loading='lazy';image.decoding='async'});

// Guest name is treated as text only, never as markup.
const guest=(new URLSearchParams(location.search).get('to')||'').replace(/[<>]/g,'').trim().slice(0,70);
if(guest)$('#guest').textContent=guest;

// Shared environmental state: every light object reads the same wind and gust.
const environment={time:0,strength:.42,direction:1,gust:0,targetGust:0,scrollVelocity:0};
const living=$$('[data-wind]').map((el,index)=>({el,sensitivity:+el.dataset.wind||.25,phase:index*1.731,amplitude:mobile?5:8,rotation:mobile?1.2:2.1}));
let nextGust=performance.now()+5000;
let lastScroll=scrollY,lastScrollTime=performance.now(),pointerX=0,pointerY=0;
document.addEventListener('pointermove',event=>{pointerX=event.clientX/innerWidth-.5;pointerY=event.clientY/innerHeight-.5;document.documentElement.style.setProperty('--mouse-x',pointerX.toFixed(3));document.documentElement.style.setProperty('--mouse-y',pointerY.toFixed(3))},{passive:true});
addEventListener('scroll',()=>{const now=performance.now(),delta=scrollY-lastScroll;environment.scrollVelocity=Math.max(-1.5,Math.min(1.5,delta/Math.max(16,now-lastScrollTime)));lastScroll=scrollY;lastScrollTime=now},{passive:true});

const activeScenes=new Set();
const sceneObserver=new IntersectionObserver(entries=>entries.forEach(entry=>entry.isIntersecting?activeScenes.add(entry.target):activeScenes.delete(entry.target)),{rootMargin:'20% 0px'});
$$('.scene').forEach(scene=>sceneObserver.observe(scene));

function noise(t,phase){return Math.sin(t*.73+phase)*.52+Math.sin(t*1.17+phase*1.9)*.29+Math.sin(t*.31+phase*.4)*.19}
function animateEnvironment(now){
  if(!reduced){
    environment.time=now/1000;
    if(now>nextGust){environment.targetGust=Math.random()>.56?.45+Math.random()*.35:0;nextGust=now+5500+Math.random()*8000}
    environment.gust+=(environment.targetGust-environment.gust)*.008;
    if(environment.gust>.65)environment.targetGust=0;
    const wind=environment.strength+environment.gust+Math.min(.25,Math.abs(environment.scrollVelocity)*.12);
    living.forEach(item=>{
      if(!item.el.closest('.scene')||activeScenes.has(item.el.closest('.scene'))){
        const n=noise(environment.time,item.phase),n2=noise(environment.time*.58,item.phase+4);
        const x=(n*item.amplitude*item.sensitivity+pointerX*2*item.sensitivity)*wind;
        const y=n2*item.amplitude*.38*item.sensitivity;
        const r=n2*item.rotation*item.sensitivity*wind;
        item.el.style.setProperty('--wx',`${x.toFixed(2)}px`);item.el.style.setProperty('--wy',`${y.toFixed(2)}px`);item.el.style.setProperty('--wr',`${r.toFixed(2)}deg`);
      }
    });
    environment.scrollVelocity*=.92;
  }
  requestAnimationFrame(animateEnvironment);
}
requestAnimationFrame(animateEnvironment);

// Depth-based camera movement. Distant scenery travels less than foreground foliage.
let parallaxQueued=false;
function renderParallax(){
  activeScenes.forEach(scene=>{
    const rect=scene.getBoundingClientRect(),progress=(innerHeight-rect.top)/(innerHeight+rect.height)-.5;
    $$('[data-depth]',scene).forEach(el=>{const depth=+el.dataset.depth||.2;el.style.setProperty('--py',`${(-progress*70*depth).toFixed(2)}px`);el.style.setProperty('--px',`${(pointerX*10*depth).toFixed(2)}px`)});
  });parallaxQueued=false;
}
addEventListener('scroll',()=>{if(!parallaxQueued&&!reduced){parallaxQueued=true;requestAnimationFrame(renderParallax)}},{passive:true});
addEventListener('resize',renderParallax,{passive:true});renderParallax();

// Recycled petal pool with flutter, depth, lateral wind and occasional upward lift.
const petalHost=$('.petal-field'),petals=[];
const petalCount=reduced?0:(lowPower?7:mobile?11:19);
function resetPetal(p,first=false){p.x=Math.random()*innerWidth;p.y=first?Math.random()*innerHeight:-40-Math.random()*innerHeight*.3;p.z=Math.random();p.speed=.22+Math.random()*.5;p.swing=10+Math.random()*34;p.phase=Math.random()*10;p.spin=Math.random()*360;p.el.className=`petal ${p.z>.78?'near':p.z<.28?'far':''}`}
for(let i=0;i<petalCount;i++){const el=document.createElement('i');petalHost.append(el);const p={el};resetPetal(p,true);petals.push(p)}
function animatePetals(now){if(!reduced){const t=now/1000;petals.forEach(p=>{p.y+=p.speed*(1+p.z)*(.85+Math.max(0,environment.scrollVelocity*.3));p.x+=Math.sin(t*.8+p.phase)*.18+environment.gust*.26*environment.direction;p.spin+=.35+p.z*.6;const flutter=Math.sin(t*2.1+p.phase)*p.swing;p.el.style.transform=`translate3d(${p.x+flutter}px,${p.y}px,0) rotate(${p.spin}deg) rotateY(${Math.sin(t*3+p.phase)*70}deg)`;if(p.y>innerHeight+60||p.x>innerWidth+80||p.x<-100)resetPetal(p)})}requestAnimationFrame(animatePetals)}requestAnimationFrame(animatePetals);

// Cinematic envelope opening: foliage reacts first, then the clean envelope reveals the invitation.
const opening=$('#opening'),openButton=$('.open-btn',opening);
function dismissOpening(){
  opening.remove();
  document.body.classList.remove('invitation-locked');
  scrollTo({top:0,left:0,behavior:'auto'});
  requestAnimationFrame(()=>{
    scrollTo({top:0,left:0,behavior:'auto'});
    if(window.ScrollTrigger)ScrollTrigger.refresh();
  });
}
openButton.addEventListener('click',()=>{
  opening.classList.add('opened');environment.targetGust=.9;
  if(window.gsap&&!reduced){
    const tl=gsap.timeline({defaults:{ease:'power3.inOut'}});
    tl.to('.flower-left',{x:-65,rotation:-4,duration:1.2},0).to('.leaves-right',{x:75,rotation:5,duration:1.2},0)
      .to('.envelope-object',{rotationX:-5,scale:1.04,duration:.7},.35)
      .to('.opening-copy',{opacity:0,y:-30,duration:.7},.6).to(openButton,{opacity:0,y:12,duration:.45},.6)
      .to('.envelope-stage',{scale:2.5,y:'-32%',opacity:0,duration:1.2,ease:'expo.inOut'},1.55)
      .to(opening,{backgroundColor:'#f5f0e8',duration:.7},1.8)
      .add(dismissOpening,2.15);
  }else{setTimeout(dismissOpening,900)}
  startAmbient();
},{once:true});

// A quiet generated ambient chord preserves the no-autoplay rule without shipping a heavy audio file.
let audioContext=null,ambientGain=null,ambientOn=false;
function startAmbient(){if(ambientOn)return;try{audioContext=new(window.AudioContext||window.webkitAudioContext)();ambientGain=audioContext.createGain();ambientGain.gain.setValueAtTime(0,audioContext.currentTime);ambientGain.gain.linearRampToValueAtTime(.018,audioContext.currentTime+2.8);ambientGain.connect(audioContext.destination);[174,220,261.63].forEach((frequency,index)=>{const oscillator=audioContext.createOscillator(),gain=audioContext.createGain();oscillator.type='sine';oscillator.frequency.value=frequency;gain.gain.value=[.55,.32,.18][index];oscillator.connect(gain).connect(ambientGain);oscillator.start()});ambientOn=true;$('.music').setAttribute('aria-pressed','true');$('.music i').textContent='Music on'}catch{}}
$('.music').addEventListener('click',function(){if(!audioContext){startAmbient();return}const on=this.getAttribute('aria-pressed')==='true';const target=on?0:.018;ambientGain.gain.cancelScheduledValues(audioContext.currentTime);ambientGain.gain.linearRampToValueAtTime(target,audioContext.currentTime+.7);this.setAttribute('aria-pressed',String(!on));$('i',this).textContent=on?'Music off':'Music on'});

const goal=new Date('2027-01-18T08:00:00+07:00');
function tick(){let remaining=Math.max(0,goal-new Date());[['days',86400000],['hours',3600000],['minutes',60000],['seconds',1000]].forEach(([id,unit])=>{const value=Math.floor(remaining/unit);remaining%=unit;$('#'+id).textContent=String(value).padStart(id==='days'?3:2,'0')})}tick();setInterval(tick,1000);

$('.copy-account').addEventListener('click',async function(){try{await navigator.clipboard.writeText(this.dataset.account);this.textContent='COPIED';setTimeout(()=>this.textContent='COPY ACCOUNT',1600)}catch{this.textContent=this.dataset.account}});
$('#rsvp-form').addEventListener('submit',event=>{event.preventDefault();const data=new FormData(event.currentTarget),name=String(data.get('name')||'').trim().replace(/[<>]/g,'');$('.form-note').textContent=`Terima kasih, ${name}. Kehadiran dan doa Anda sangat berarti.`;if(name){const wish=document.createElement('blockquote');wish.textContent=`“${String(data.get('message')||'Semoga berbahagia selalu.').replace(/[<>]/g,'')}”`;const cite=document.createElement('cite');cite.textContent=`— ${name}`;wish.append(cite);$('#wishes').append(wish)}event.currentTarget.reset()});

const lightbox=$('.lightbox');
function closeLightbox(){lightbox.close();lightbox.hidden=true}
$$('.memory-photo').forEach(button=>button.addEventListener('click',()=>{const source=$('img',button);$('img',lightbox).src=source.src;$('img',lightbox).alt=source.alt;lightbox.hidden=false;lightbox.showModal()}));
$('button',lightbox).addEventListener('click',closeLightbox);
lightbox.addEventListener('click',event=>{if(event.target===lightbox)closeLightbox()});
lightbox.addEventListener('cancel',()=>{lightbox.hidden=true});

// Section choreography uses GSAP/ScrollTrigger when available, with a usable static fallback.
if(window.gsap&&window.ScrollTrigger&&!reduced){
  gsap.registerPlugin(ScrollTrigger);
  $$('.scene').forEach((scene,index)=>{
    const content=$$('.content-layer',scene);
    gsap.from(content,{scrollTrigger:{trigger:scene,start:'top 78%',once:true},y:index%2?42:55,opacity:0,duration:1.25,stagger:.12,ease:'power3.out'});
  });
  gsap.from('.portrait.groom',{scrollTrigger:{trigger:'#couple',start:'top 65%'},x:-70,opacity:0,duration:1.5,ease:'power3.out'});
  gsap.from('.portrait.bride',{scrollTrigger:{trigger:'#couple',start:'top 65%'},x:70,opacity:0,duration:1.5,ease:'power3.out'});
  gsap.from('.ampersand',{scrollTrigger:{trigger:'#couple',start:'top 55%'},scale:.2,opacity:0,duration:1.1,delay:.35,ease:'power2.out'});
  $$('.story-moment').forEach((moment,index)=>gsap.from(moment,{scrollTrigger:{trigger:moment,start:'top 76%'},x:index%2?-45:45,opacity:0,duration:1.15,ease:'power3.out'}));
  gsap.from('.gift-object',{scrollTrigger:{trigger:'#gift',start:'top 65%'},y:35,rotation:-3,scale:.9,opacity:0,duration:1.6,ease:'expo.out'});
  gsap.to('.closing-photo',{scrollTrigger:{trigger:'#closing',start:'top bottom',end:'bottom bottom',scrub:1.2},scale:.88,opacity:.58,ease:'none'});
}else{$$('.content-layer').forEach(element=>element.style.opacity=1)}
