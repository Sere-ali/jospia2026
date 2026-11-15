/* Décor 3D temps réel de la présentation (Three.js) — thème islamique :
   calligraphie d'Allah et de Muhammad ﷺ, mosquée, minarets, dômes, croissants,
   étoiles à huit pointes, lanternes, chapelet. Métal doré, lumière réelle, caméra cinématique. */
import * as THREE from 'three';
import {RoomEnvironment} from './RoomEnvironment.js';
import {SVGLoader} from './SVGLoader.js';

const cv = document.getElementById('g3d');
let R;
try { R = new THREE.WebGLRenderer({canvas: cv, alpha: true, antialias: true, powerPreference: 'high-performance'}); } catch (e) { R = null; }
if (R) {
  const W = 1600, H = 900;
  R.setPixelRatio(1); R.setSize(W, H, false);
  R.toneMapping = THREE.ACESFilmicToneMapping; R.toneMappingExposure = 1.2;
  R.setClearColor(0x000000, 0);

  const scene = new THREE.Scene();
  scene.fog = new THREE.FogExp2(0x03141a, 0.03);
  const pm = new THREE.PMREMGenerator(R);
  scene.environment = pm.fromScene(new RoomEnvironment(), 0.04).texture;
  scene.environmentIntensity = 0.6;
  const cam = new THREE.PerspectiveCamera(45, W / H, 0.1, 90);
  cam.position.set(0, 0, 15);

  const lo = new THREE.PointLight(0xffa24a, 440, 0, 2); lo.position.set(-9, 5, 7); scene.add(lo);
  const lt = new THREE.PointLight(0x2fd8c4, 340, 0, 2); lt.position.set(9, -4, 6); scene.add(lt);
  const lw = new THREE.PointLight(0xfff1d6, 150, 0, 2); lw.position.set(0, 9, 8); scene.add(lw);
  scene.add(new THREE.AmbientLight(0x4a7c86, 0.35));

  /* ---------- matériaux ---------- */
  const gold = (e = .12) => new THREE.MeshPhysicalMaterial({color: 0xf2b640, metalness: 1, roughness: .2, clearcoat: .7, clearcoatRoughness: .1, emissive: 0x7a4300, emissiveIntensity: e});
  const jade = () => new THREE.MeshPhysicalMaterial({color: 0x0fa090, metalness: .55, roughness: .18, clearcoat: 1, clearcoatRoughness: .06, emissive: 0x04463e, emissiveIntensity: .25});
  const ivory = () => new THREE.MeshPhysicalMaterial({color: 0xf3ead8, metalness: .1, roughness: .45, clearcoat: .3});
  const glow = (c = 0xffb347) => new THREE.MeshBasicMaterial({color: c, fog: false});

  /* ---------- géométries ---------- */
  function lathe(pts, seg = 40) { return new THREE.LatheGeometry(pts.map(p => new THREE.Vector2(p[0], p[1])), seg); }
  function domeGeo() {
    const p = [[0, 0]];
    for (let i = 0; i <= 26; i++) { const t = i / 26; p.push([Math.pow(1 - t, .55) * (1 + .2 * Math.sin(Math.PI * Math.min(1, t * 1.15))), t * 1.9]); }
    return lathe(p, 48);
  }
  const DOME = domeGeo();
  const MIN_BODY = lathe([[0, 0], [.34, 0], [.34, .2], [.27, 3.2], [.27, 3.26], [.47, 3.32], [.47, 3.5], [.3, 3.56], [.25, 3.7], [.25, 4.1]], 28);
  const MIN_TOP = lathe([[.3, 4.1], [.32, 4.16], [.22, 4.3], [.1, 4.9], [.03, 5.3], [0, 5.5]], 28);
  const FAN_BODY = lathe([[0, 0], [.26, 0], [.32, .1], [.58, .5], [.62, .9], [.5, 1.3], [.3, 1.5], [.2, 1.6], [.2, 1.78], [.28, 1.82], [0, 2.05]], 8);
  const FAN_GLASS = new THREE.CylinderGeometry(.4, .46, .85, 14, 1, true);

  function ext(shape, depth = .22, bev = .05) { const g = new THREE.ExtrudeGeometry(shape, {depth, bevelEnabled: true, bevelThickness: bev, bevelSize: bev, bevelSegments: 4, curveSegments: 40}); g.center(); return g; }
  function star8(hole = .3) {
    const s = new THREE.Shape(), a = .7654;
    for (let k = 0; k < 16; k++) { const r = k % 2 ? a : 1, th = k * Math.PI / 8 + Math.PI / 2, x = Math.cos(th) * r, y = Math.sin(th) * r; k ? s.lineTo(x, y) : s.moveTo(x, y); }
    s.closePath();
    const h = new THREE.Path(); h.absarc(0, 0, hole, 0, Math.PI * 2, true); s.holes.push(h);
    return s;
  }
  function crescentShape() {
    const s = new THREE.Shape(), th = .81, a = 1.134;
    s.moveTo(Math.cos(th), Math.sin(th));
    s.absarc(0, 0, 1, th, Math.PI * 2 - th, false);
    s.absarc(.35, 0, .8, -a, a, true);
    s.closePath(); return s;
  }
  const STAR = ext(star8(.32), .18, .04), CRES = ext(crescentShape(), .2, .045), HALO = ext(star8(.88), .08, .025);

  /* ---------- objets composés ---------- */
  function mosque() {
    const g = new THREE.Group(), G = gold(), I = ivory(), J = jade();
    const add = (geo, mat, x, y, z, s = 1) => { const m = new THREE.Mesh(geo, mat); m.position.set(x, y, z); m.scale.setScalar(s); g.add(m); return m; };
    add(new THREE.BoxGeometry(4.4, 1.3, 2.3), I, 0, .65, 0);
    add(new THREE.CylinderGeometry(.95, 1.05, .7, 40), I, 0, 1.65, 0);
    add(DOME, G, 0, 2.0, 0, .95);
    add(new THREE.SphereGeometry(.07, 12, 12), G, 0, 3.85, 0);
    [-1, 1].forEach(sx => {
      add(MIN_BODY, I, sx * 2.55, 0, 0, .78); add(MIN_TOP, G, sx * 2.55, 0, 0, .78);
      add(DOME, G, sx * 1.45, 1.3, .35, .42);
      add(new THREE.CylinderGeometry(.42, .42, .3, 24), I, sx * 1.45, 1.15, .35);
    });
    for (let i = -3; i <= 3; i++) { const w = new THREE.Mesh(new THREE.PlaneGeometry(.26, .6), glow()); w.position.set(i * .62, .62, 1.16); g.add(w); const t = new THREE.Mesh(new THREE.CircleGeometry(.13, 16), glow()); t.position.set(i * .62, .92, 1.16); g.add(t); }
    add(new THREE.BoxGeometry(4.7, .12, 2.5), J, 0, .06, 0);
    g.userData.up = true; return g;
  }
  function lantern() {
    const g = new THREE.Group();
    g.add(new THREE.Mesh(FAN_BODY, gold(.2)));
    const gl = new THREE.Mesh(FAN_GLASS, new THREE.MeshBasicMaterial({color: 0xffa640, transparent: true, opacity: .85, side: THREE.DoubleSide, fog: false})); gl.position.y = .85; g.add(gl);
    const r = new THREE.Mesh(new THREE.TorusGeometry(.22, .035, 10, 24), gold()); r.position.y = 2.2; g.add(r);
    g.userData.up = true; return g;
  }
  function tasbih() {
    const g = new THREE.Group(), N = 33, im = new THREE.InstancedMesh(new THREE.SphereGeometry(.1, 20, 16), jade(), N), m4 = new THREE.Matrix4();
    for (let i = 0; i < N; i++) { const a = i / N * Math.PI * 2; m4.makeTranslation(Math.cos(a) * 1, Math.sin(a) * 1, 0); im.setMatrixAt(i, m4); }
    g.add(im);
    const big = new THREE.Mesh(new THREE.SphereGeometry(.17, 20, 16), gold()); big.position.set(0, -1, 0); g.add(big);
    const tas = new THREE.Mesh(new THREE.ConeGeometry(.1, .7, 14), gold()); tas.position.set(0, -1.5, 0); tas.rotation.x = Math.PI; g.add(tas);
    return g;
  }
  function crescent() { const g = new THREE.Group(); g.add(new THREE.Mesh(CRES, gold())); const s = new THREE.Mesh(star8(.0 + .001), jade()); return g; }

  /* ---------- calligraphie : contours de la police Amiri, extrudés en 3D ---------- */
  const callig = {allah: new THREE.Group(), muhammad: new THREE.Group(), salat: new THREE.Group()};
  fetch(new URL('./calli.json', import.meta.url)).then(r => r.json()).then(j => {
    const ldr = new SVGLoader();
    for (const k of Object.keys(callig)) {
      const svg = ldr.parse('<svg xmlns="http://www.w3.org/2000/svg"><path d="' + j[k].d + '"/></svg>');
      const shapes = []; svg.paths.forEach(p => SVGLoader.createShapes(p).forEach(s => shapes.push(s)));
      const g = new THREE.ExtrudeGeometry(shapes, {depth: 95, bevelEnabled: true, bevelThickness: 16, bevelSize: 12, bevelSegments: 3, curveSegments: 10});
      g.center(); g.computeBoundingBox(); const h = g.boundingBox.max.y - g.boundingBox.min.y; g.scale(1 / h, 1 / h, 1 / h);
      callig[k].add(new THREE.Mesh(g, gold(.28)));
    }
  }).catch(() => {});

  /* ---------- champ d'objets flottants ---------- */
  const shapes = [];
  let seed = 11; const rnd = () => (seed = (seed * 16807) % 2147483647) / 2147483647;
  const kinds = ['star', 'star', 'crescent', 'crescent', 'lantern', 'lantern', 'dome', 'minaret', 'tasbih', 'mosque', 'star', 'crescent', 'lantern', 'dome', 'minaret', 'tasbih'];
  function make(kind) {
    const g = new THREE.Group(), jd = rnd() < .35;
    switch (kind) {
      case 'star': g.add(new THREE.Mesh(STAR, jd ? jade() : gold())); break;
      case 'crescent': g.add(new THREE.Mesh(CRES, jd ? jade() : gold())); break;
      case 'lantern': return lantern();
      case 'dome': { const d = new THREE.Mesh(DOME, jd ? jade() : gold()); d.position.y = -.9; g.add(d); g.userData.up = true; break; }
      case 'minaret': { const a = new THREE.Mesh(MIN_BODY, ivory()), b = new THREE.Mesh(MIN_TOP, gold()); a.position.y = b.position.y = -2.5; g.add(a, b); g.userData.up = true; break; }
      case 'tasbih': return tasbih();
      case 'mosque': { const m = mosque(); m.position.y = -1.2; g.add(m); g.userData.up = true; break; }
    }
    return g;
  }
  const NF = 26;
  for (let i = 0; i < NF; i++) {
    let x, y, z, t = 0;
    do { x = (rnd() - .5) * 40; y = (rnd() - .5) * 21; z = -17 + rnd() * 11; t++; } while (t < 40 && Math.abs(x) < 10 && Math.abs(y) < 6.2 && z > -10);
    const k = kinds[i % kinds.length], o = make(k);
    const sc = (k === 'mosque' ? .32 : k === 'minaret' ? .4 : .42) + rnd() * .4; o.scale.setScalar(sc);
    o.position.set(x, y, z); o.rotation.set(0, rnd() * 6, k === 'star' || k === 'crescent' ? rnd() * 6 : 0);
    if (!o.userData.up && (k === 'tasbih')) o.rotation.set(rnd() * 3, rnd() * 3, rnd() * 3);
    scene.add(o);
    shapes.push({o, home: o.position.clone(), v: new THREE.Vector3(), up: !!o.userData.up, kind: k, ph: rnd() * 6, rs: .25 + rnd() * .5});
  }
  /* calligraphies fixes dans les marges, toujours droites et de face (jamais culbutées) */
  const fixed = [];
  [['allah', -13.2, 6.2, -7, 1.9], ['muhammad', 13.4, 5.6, -8, 1.9], ['allah', 14, -6.2, -9, 1.5], ['muhammad', -14.4, -6, -9, 1.5]].forEach(([k, x, y, z, s]) => {
    const g = callig[k] === undefined ? null : new THREE.Group();
    const src = callig[k]; const holder = new THREE.Group(); holder.userData.src = k;
    holder.position.set(x, y, z); holder.scale.setScalar(s); scene.add(holder);
    fixed.push({holder, k, home: holder.position.clone(), ph: rnd() * 6, got: false});
  });

  /* ---------- objets vedettes ---------- */
  const heroA = new THREE.Group(), heroM = new THREE.Group(), heroS = new THREE.Group(), heroMq = mosque(), halo = new THREE.Mesh(HALO, gold(.35));
  const hG = [heroA, heroM, heroMq, halo]; hG.forEach(h => { h.scale.setScalar(.001); h.visible = false; scene.add(h); });
  scene.add(heroS); heroS.scale.setScalar(.001);
  const halo2 = new THREE.Mesh(HALO, jade()); halo2.scale.setScalar(.001); scene.add(halo2);

  /* poussière d'étoiles */
  const N = 700, pa = new Float32Array(N * 3);
  for (let i = 0; i < N; i++) { pa[i * 3] = (rnd() - .5) * 60; pa[i * 3 + 1] = (rnd() - .5) * 34; pa[i * 3 + 2] = -25 + rnd() * 40; }
  const pg = new THREE.BufferGeometry(); pg.setAttribute('position', new THREE.BufferAttribute(pa, 3));
  const stars = new THREE.Points(pg, new THREE.PointsMaterial({color: 0xffe9b8, size: .07, transparent: true, opacity: .85, blending: THREE.AdditiveBlending, depthWrite: false}));
  scene.add(stars);

  /* ---------- pilotage par la présentation ---------- */
  const T = {A: {p: new THREE.Vector3(8, 0, -5), s: 0}, M: {p: new THREE.Vector3(-8, 0, -5), s: 0}, Mq: {p: new THREE.Vector3(0, -2, -6), s: 0}, H: {p: new THREE.Vector3(0, 0, -9), s: 0}};
  const S = {show: true, idx: 0, kind: 'c', kick: 0, flash: 0, camT: new THREE.Vector3(0, 0, 15), mx: 0, my: 0};
  addEventListener('mousemove', e => { S.mx = e.clientX / innerWidth - .5; S.my = e.clientY / innerHeight - .5; });
  const set = (o, x, y, z, s) => { o.p.set(x, y, z); o.s = s; };

  window.P3 = {
    resize(w, h) { R.setSize(Math.round(w * .83), Math.round(h * .83), false); cam.aspect = w / h; cam.fov = w < h ? 74 : 45; cam.updateProjectionMatrix(); },
    slide(i, el) {
      const c = el.classList;
      S.kind = c.contains('intro') ? 'intro' : c.contains('fin') ? 'fin' : c.contains('chap') ? 'chap' : c.contains('ann') ? 'ann' : 'c';
      const th = i * .42;
      S.camT.set(Math.sin(th) * 3.2, Math.cos(th * 1.3) * 1.6, 14.5 + Math.sin(th * .7) * 1.4);
      S.kick = S.kind === 'chap' ? 4.5 : S.kind === 'c' ? 2.5 : 4; S.flash = 1; S.idx = i;
      S.show = S.kind === 'intro' || S.kind === 'chap' || S.kind === 'fin'; cv.style.opacity = S.show ? 1 : 0; /* 3D seulement sur les pages de grands titres */
      const teal = c.contains('b') && c.contains('site');
      lo.color.set(teal ? 0x2fd8c4 : 0xffa24a); lt.color.set(teal ? 0xffa24a : 0x2fd8c4);
      const V = document.body.classList.contains('vert');
      set(T.A, 0, 0, 0, 0); set(T.M, 0, 0, 0, 0); set(T.Mq, 0, -2, -6, 0); set(T.H, 0, 0, -10, 0);
      if (V && (S.kind === 'intro' || S.kind === 'fin')) { set(T.A, 0, -10.4, -5, 4.2); set(T.M, 0, 10.6, -5, 3.8); set(T.H, 0, 0, -11, 6); }
      else if (V && S.kind === 'chap') { set(T.Mq, 0, -10.8, -6.5, .95); set(T.H, 0, .6, -11, 6); }
      else if (S.kind === 'intro' || S.kind === 'fin') { if (S.kind === 'intro') { set(T.A, 10.4, -5.2, -5, 3.7); set(T.M, -10.4, -5.2, -5, 3.4); } else { set(T.A, 11.6, .6, -5, 4.2); set(T.M, -11.6, .9, -5, 3.9); } set(T.H, 0, 0, -11, 8.5); }
      else if (S.kind === 'chap') { set(T.Mq, 0, -6.6, -6.5, .95); set(T.H, 0, .6, -11, 9); }
      else if (S.kind === 'ann') { set(T.Mq, 10.2, -4.6, -4, .5); }
      shapes.forEach(s => s.v.addScaledVector(s.o.position.clone().sub(new THREE.Vector3(0, 0, 4)).normalize(), S.kind === 'chap' ? .5 : .2));
    },
    step() { S.flash = .7; shapes.forEach(s => s.v.addScaledVector(s.o.position.clone().normalize(), .08)); },
    burst(x, y) {
      S.flash = 1;
      const b = new THREE.Vector3((x / 1920 - .5) * 20, -(y / 1080 - .5) * 11, 2);
      shapes.forEach(s => { const d = s.o.position.clone().sub(b); s.v.addScaledVector(d.normalize(), 1.3 / (1 + d.length() * .12)); });
    }
  };
  P3.resize(document.body.classList.contains('vert') ? 1080 : 1920, document.body.classList.contains('vert') ? 1920 : 1080);
  const cur = document.querySelector('.slide.actif'), all = [].slice.call(document.querySelectorAll('.slide'));
  if (cur) P3.slide(all.indexOf(cur), cur);
  document.body.classList.add('g3dok');

  /* ---------- boucle de rendu ---------- */
  const clock = new THREE.Clock(), tmp = new THREE.Vector3(); let cvOff = false; setInterval(() => { cvOff = !S.show; }, 900);
  function glide(o, tg, k, sway, t, ph) {
    o.position.lerp(tg.p, k); const s = o.scale.x + (tg.s * (1 + S.flash * .06) - o.scale.x) * k; o.scale.setScalar(Math.max(s, .001));
    o.visible = s > .02;
    o.rotation.y = Math.sin(t * .5 + ph) * sway; o.rotation.x = Math.sin(t * .37 + ph) * sway * .25;
  }
  (function tick() {
    requestAnimationFrame(tick);
    if (document.hidden) return;
    if (!S.show && cv.style.opacity === '0') { clock.getDelta(); if (cvOff) return; }
    const dt = Math.min(clock.getDelta(), .05), t = clock.elapsedTime, k = 1 - Math.pow(.04, dt);
    S.kick *= Math.pow(.04, dt);
    tmp.copy(S.camT); tmp.z += S.kick; tmp.x += S.mx * 2.4; tmp.y -= S.my * 1.5;
    cam.position.lerp(tmp, 1 - Math.pow(.02, dt));
    cam.lookAt(S.mx * .8, -S.my * .5, 0);
    shapes.forEach(s => {
      const o = s.o;
      s.v.addScaledVector(s.home.clone().sub(o.position), .5 * dt); s.v.multiplyScalar(Math.pow(.35, dt));
      o.position.addScaledVector(s.v, 60 * dt); o.position.y += Math.sin(t * .6 + s.ph) * .004;
      const sp = dt * s.rs * (1 + S.kick * .15);
      if (s.kind === 'star') { o.rotation.z += sp; o.rotation.y = Math.sin(t * .5 + s.ph) * .6; }
      else if (s.kind === 'crescent') { o.rotation.y = Math.sin(t * .45 + s.ph) * .7; o.rotation.z = Math.sin(t * .3 + s.ph) * .35; }
      else if (s.up) { o.rotation.y += sp; }
      else { o.rotation.x += sp; o.rotation.y += sp * 1.3; }
    });
    /* calligraphies de marge : on branche la géométrie une fois chargée, balancement très doux */
    fixed.forEach(f => {
      if (!f.got && callig[f.k].children.length) { f.holder.add(callig[f.k].children[0].clone()); f.got = true; }
      f.holder.rotation.y = Math.sin(t * .35 + f.ph) * .28; f.holder.position.y = f.home.y + Math.sin(t * .5 + f.ph) * .18;
    });
    /* vedettes : calligraphie d'Allah et de Muhammad ﷺ, mosquée, étoile-halo */
    [['allah', heroA, T.A], ['muhammad', heroM, T.M]].forEach(([n, h, tg]) => { if (!h.children.length && callig[n].children.length) h.add(callig[n].children[0].clone()); });
    if (!heroS.children.length && callig.salat.children.length) { heroS.add(callig.salat.children[0].clone()); }
    glide(heroA, T.A, k, .3, t, 0); glide(heroM, T.M, k, .3, t, 2);
    heroS.position.copy(heroM.position).add(new THREE.Vector3(0, -heroM.scale.x * .62, 0)); heroS.scale.setScalar(Math.max(heroM.scale.x * .55, .001)); heroS.visible = heroM.visible; heroS.rotation.copy(heroM.rotation);
    glide(heroMq, T.Mq, k, .45, t, 1);
    glide(halo, T.H, k, 0, t, 0); halo.rotation.z += dt * .12;
    halo2.position.copy(halo.position).add(new THREE.Vector3(0, 0, -.6)); halo2.scale.setScalar(halo.scale.x * .82); halo2.visible = halo.visible; halo2.rotation.z = -halo.rotation.z * 1.4;
    S.flash *= Math.pow(.05, dt);
    lo.intensity = 440 + S.flash * 520; lt.intensity = 340 + S.flash * 380;
    lo.position.x = -9 + Math.sin(t * .5) * 3; lt.position.y = -4 + Math.cos(t * .4) * 3;
    stars.rotation.y = t * .012;
    R.render(scene, cam);
  })();
}
