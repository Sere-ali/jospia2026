/* Décor 3D temps réel de la présentation (Three.js) : objets métalliques et cristaux
   éclairés par une vraie lumière, caméra cinématique, étincelles, explosions. */
import * as THREE from 'three';
import {RoomEnvironment} from './RoomEnvironment.js';

const cv = document.getElementById('g3d');
let R;
try { R = new THREE.WebGLRenderer({canvas: cv, alpha: true, antialias: true, powerPreference: 'high-performance'}); }
catch (e) { R = null; }
if (R) {
  const W = 1600, H = 900;
  R.setPixelRatio(1); R.setSize(W, H, false);
  R.toneMapping = THREE.ACESFilmicToneMapping; R.toneMappingExposure = 1.15;
  R.setClearColor(0x000000, 0);

  const scene = new THREE.Scene();
  scene.fog = new THREE.FogExp2(0x03141a, 0.036);
  const pm = new THREE.PMREMGenerator(R);
  scene.environment = pm.fromScene(new RoomEnvironment(), 0.04).texture;
  scene.environmentIntensity = 0.55;

  const cam = new THREE.PerspectiveCamera(45, W / H, 0.1, 80);
  cam.position.set(0, 0, 15);

  /* lumières : orange chaud à gauche, turquoise froid à droite, reflet blanc au-dessus */
  const lo = new THREE.PointLight(0xff7a1a, 420, 0, 2); lo.position.set(-9, 5, 7); scene.add(lo);
  const lt = new THREE.PointLight(0x12c2b0, 360, 0, 2); lt.position.set(9, -4, 6); scene.add(lt);
  const lw = new THREE.PointLight(0xffffff, 140, 0, 2); lw.position.set(0, 9, 8); scene.add(lw);
  scene.add(new THREE.AmbientLight(0x4a7c86, 0.35));

  const PAL = [0xff7a1a, 0x12c2b0, 0xe8f4f4, 0xe8c25a, 0xff9a3d, 0x0fa090];
  const geos = [
    new THREE.IcosahedronGeometry(1, 0), new THREE.OctahedronGeometry(1.1, 0), new THREE.TorusGeometry(.9, .32, 20, 48),
    new THREE.SphereGeometry(.8, 40, 28), new THREE.DodecahedronGeometry(1, 0), new THREE.TorusKnotGeometry(.7, .22, 90, 14)
  ];
  const shapes = [];
  let seed = 7; const rnd = () => (seed = (seed * 16807) % 2147483647) / 2147483647;
  for (let i = 0; i < 52; i++) {
    let x, y, z, t = 0;
    do { x = (rnd() - .5) * 38; y = (rnd() - .5) * 20; z = -16 + rnd() * 20; t++; }
    while (t < 30 && Math.abs(x) < 7 && Math.abs(y) < 4.2);
    const col = PAL[i % PAL.length], glass = i % 4 === 0;
    const m = new THREE.MeshPhysicalMaterial({
      color: col, metalness: glass ? 0.1 : 0.92, roughness: glass ? 0.06 : 0.16,
      clearcoat: 1, clearcoatRoughness: .08, flatShading: i % 3 === 0,
      transmission: glass ? 0.6 : 0, thickness: glass ? 1.4 : 0, ior: 1.45,
      emissive: col, emissiveIntensity: glass ? 0.12 : 0.05
    });
    const o = new THREE.Mesh(geos[i % geos.length], m);
    const sc = .35 + rnd() * .95; o.scale.setScalar(sc);
    o.position.set(x, y, z);
    o.rotation.set(rnd() * 6, rnd() * 6, rnd() * 6);
    scene.add(o);
    shapes.push({o, home: o.position.clone(), v: new THREE.Vector3(), rs: new THREE.Vector3((rnd() - .5) * .8, (rnd() - .5) * .8, (rnd() - .5) * .5), ph: rnd() * 6, sc});
  }

  /* objet vedette : nœud métallique */
  const hero = new THREE.Mesh(new THREE.TorusKnotGeometry(1.5, .46, 260, 36, 2, 3),
    new THREE.MeshPhysicalMaterial({color: 0xff7a1a, metalness: 1, roughness: .12, clearcoat: 1, clearcoatRoughness: .05, emissive: 0xff5e00, emissiveIntensity: .12}));
  hero.scale.setScalar(.001); scene.add(hero);
  const ring = new THREE.Mesh(new THREE.TorusGeometry(3.3, .035, 12, 160),
    new THREE.MeshBasicMaterial({color: 0x5ff0df, transparent: true, opacity: .8, fog: false}));
  ring.scale.setScalar(.001); scene.add(ring);
  const ring2 = ring.clone(); ring2.material = new THREE.MeshBasicMaterial({color: 0xffb36b, transparent: true, opacity: .7, fog: false}); scene.add(ring2);

  /* poussière d'étoiles */
  const N = 700, pa = new Float32Array(N * 3);
  for (let i = 0; i < N; i++) { pa[i * 3] = (rnd() - .5) * 60; pa[i * 3 + 1] = (rnd() - .5) * 34; pa[i * 3 + 2] = -25 + rnd() * 40; }
  const pg = new THREE.BufferGeometry(); pg.setAttribute('position', new THREE.BufferAttribute(pa, 3));
  const stars = new THREE.Points(pg, new THREE.PointsMaterial({color: 0xbff7ef, size: .07, transparent: true, opacity: .85, blending: THREE.AdditiveBlending, depthWrite: false, sizeAttenuation: true}));
  scene.add(stars);

  /* état piloté par la présentation */
  const S = {idx: 0, kind: 'c', hue: 0, kick: 0, flash: 0, camT: new THREE.Vector3(0, 0, 15), hero: {p: new THREE.Vector3(8, 0, 0), s: 0, rs: .6}, mx: 0, my: 0, ringS: 0};
  addEventListener('mousemove', e => { S.mx = e.clientX / innerWidth - .5; S.my = e.clientY / innerHeight - .5; });

  window.P3 = {
    slide(i, el) {
      const c = el.classList;
      S.kind = c.contains('intro') ? 'intro' : c.contains('fin') ? 'fin' : c.contains('chap') ? 'chap' : c.contains('ann') ? 'ann' : 'c';
      const th = i * .42;
      S.camT.set(Math.sin(th) * 3.6, Math.cos(th * 1.3) * 1.8, 14.5 + Math.sin(th * .7) * 1.6);
      S.kick = S.kind === 'chap' ? 7 : S.kind === 'c' ? 3 : 5;
      S.flash = 1; S.idx = i;
      const teal = c.contains('b') && c.contains('site');
      lo.color.set(teal ? 0x12c2b0 : 0xff7a1a); lt.color.set(teal ? 0xff7a1a : 0x12c2b0);
      const h = S.hero;
      if (S.kind === 'intro') { h.p.set(8.4, .2, -1); h.s = 1.25; h.rs = .5; }
      else if (S.kind === 'chap') { h.p.set(0, -.3, -4.5); h.s = 2.6; h.rs = 1.1; }
      else if (S.kind === 'fin') { h.p.set(0, 0, -2); h.s = 1.9; h.rs = .8; }
      else if (S.kind === 'ann') { h.p.set(9.5, -3.2, -2); h.s = .8; h.rs = .9; }
      else { h.p.set(12.5, -6.4, -3); h.s = .42; h.rs = .7; }
      shapes.forEach(s => s.v.addScaledVector(s.o.position.clone().sub(new THREE.Vector3(0, 0, 4)).normalize(), S.kind === 'chap' ? .55 : .22));
    },
    step() { S.flash = .7; shapes.forEach(s => s.v.addScaledVector(s.o.position.clone().normalize(), .1)); },
    burst(x, y) {
      S.flash = 1;
      const b = new THREE.Vector3((x / 1920 - .5) * 20, -(y / 1080 - .5) * 11, 2);
      shapes.forEach(s => { const d = s.o.position.clone().sub(b); const k = 1.4 / (1 + d.length() * .12); s.v.addScaledVector(d.normalize(), k); s.v.x += (Math.random() - .5) * .2; });
    }
  };
  const cur = document.querySelector('.slide.actif'), all = [].slice.call(document.querySelectorAll('.slide'));
  if (cur) P3.slide(all.indexOf(cur), cur);
  document.body.classList.add('g3dok');

  const clock = new THREE.Clock(); const tmp = new THREE.Vector3(); let ringA = 0;
  (function tick() {
    requestAnimationFrame(tick);
    if (document.hidden) return;
    const dt = Math.min(clock.getDelta(), .05), t = clock.elapsedTime;
    /* caméra cinématique : va vers sa cible, recul au changement de page, parallaxe souris */
    S.kick *= Math.pow(.04, dt);
    tmp.copy(S.camT); tmp.z += S.kick; tmp.x += S.mx * 2.4; tmp.y -= S.my * 1.5;
    cam.position.lerp(tmp, 1 - Math.pow(.02, dt));
    cam.lookAt(S.mx * .8, -S.my * .5, 0);
    cam.rotation.z += Math.sin(t * .2) * .0006;
    /* objets : flottement, rotation, ressort vers leur place */
    shapes.forEach(s => {
      const o = s.o;
      s.v.addScaledVector(s.home.clone().sub(o.position), .5 * dt);
      s.v.multiplyScalar(Math.pow(.35, dt));
      o.position.addScaledVector(s.v, 60 * dt);
      o.position.y += Math.sin(t * .6 + s.ph) * .004;
      o.rotation.x += s.rs.x * dt * (1 + S.kick * .15); o.rotation.y += s.rs.y * dt * (1 + S.kick * .15); o.rotation.z += s.rs.z * dt;
    });
    /* vedette */
    const h = S.hero;
    hero.position.lerp(h.p, 1 - Math.pow(.03, dt));
    const ts = h.s, k = 1 - Math.pow(.04, dt);
    hero.scale.setScalar(hero.scale.x + (ts * (1 + S.flash * .12) - hero.scale.x) * k);
    hero.rotation.x += dt * h.rs * .6; hero.rotation.y += dt * h.rs;
    const big = (S.kind === 'chap' || S.kind === 'intro' || S.kind === 'fin');
    ringA += dt;
    [ring, ring2].forEach((r, j) => {
      r.position.copy(hero.position);
      const rs = big ? hero.scale.x * 1.35 : .001; r.scale.setScalar(r.scale.x + (rs - r.scale.x) * k);
      r.rotation.x = 1.2 + ringA * (j ? -.5 : .4) + j; r.rotation.y = ringA * (j ? .35 : -.3);
    });
    S.flash *= Math.pow(.05, dt);
    lo.intensity = 420 + S.flash * 520; lt.intensity = 360 + S.flash * 380;
    lo.position.x = -9 + Math.sin(t * .5) * 3; lt.position.y = -4 + Math.cos(t * .4) * 3;
    stars.rotation.y = t * .012; stars.rotation.x = Math.sin(t * .05) * .05;
    R.render(scene, cam);
  })();
}
