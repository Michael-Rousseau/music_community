import * as THREE from "three";
import { EffectComposer } from "three/addons/postprocessing/EffectComposer.js";
import { RenderPass } from "three/addons/postprocessing/RenderPass.js";
import { UnrealBloomPass } from "three/addons/postprocessing/UnrealBloomPass.js";
import { OutputPass } from "three/addons/postprocessing/OutputPass.js";

window.toggleDrawer = function (show) {
  const d = document.getElementById("drawer");
  const o = document.getElementById("overlay");
  if (show) {
    d.classList.add("open");
    o.classList.add("visible");
  } else {
    d.classList.remove("open");
    o.classList.remove("visible");
  }
};

window.submitRating = function (val) {
  if (!window.isUserLoggedIn) {
    window.location.href = "/login";
  } else {
    document.getElementById("ratingInput").value = val;
    document.getElementById("ratingForm").submit();
  }
};

window.jumpTo = function (seconds) {
  const audio = document.getElementById("audio");
  audio.currentTime = seconds;
  if (audio.paused) {
    document.getElementById("playBtn").click();
  }
};

document.addEventListener("DOMContentLoaded", () => {
  const audio = document.getElementById("audio");
  const playBtn = document.getElementById("playBtn");
  const icon = playBtn.querySelector("i");
  const bar = document.getElementById("progressBar");
  const barCont = document.getElementById("progressContainer");
  const timestampInput = document.getElementById("timestampInput");
  const popup = document.getElementById("popup");
  const popupUser = document.getElementById("popupUser");
  const popupContent = document.getElementById("popupContent");

  let isPlaying = false;
  let markersCreated = false;

  playBtn.addEventListener("click", () => {
    if (!context) initAudioContext();

    if (isPlaying) {
      audio.pause();
      icon.classList.replace("fa-pause", "fa-play");
    } else {
      audio.play();
      icon.classList.replace("fa-play", "fa-pause");
    }
    isPlaying = !isPlaying;
  });

  audio.addEventListener("loadedmetadata", () => {
    if (!markersCreated && audio.duration > 0) createMarkers();
  });

  function createMarkers() {
    const duration = audio.duration;
    if (typeof commentsData !== "undefined") {
      commentsData.forEach((c) => {
        if (c.timestamp > duration) return;
        const left = (c.timestamp / duration) * 100;
        const marker = document.createElement("div");
        marker.className = "comment-marker";
        marker.style.left = left + "%";
        barCont.appendChild(marker);
      });
    }
    markersCreated = true;
  }

  audio.addEventListener("timeupdate", () => {
    if (!audio.duration) return;

    const pct = (audio.currentTime / audio.duration) * 100;
    bar.style.width = pct + "%";

    let m = Math.floor(audio.currentTime / 60),
      s = Math.floor(audio.currentTime % 60);
    document.getElementById("currTime").innerText =
      `${m}:${s < 10 ? "0" + s : s}`;

    if (timestampInput) timestampInput.value = Math.floor(audio.currentTime);

    const currentSec = audio.currentTime;
    if (typeof commentsData !== "undefined") {
      const activeComment = commentsData.find(
        (c) => Math.abs(c.timestamp - currentSec) < 0.5,
      );

      if (activeComment) {
        popupUser.innerText = activeComment.username;
        popupContent.innerText = activeComment.content;
        popup.classList.add("active");
      } else {
        popup.classList.remove("active");
      }
    }
  });

  barCont.addEventListener("click", (e) => {
    const pct = e.offsetX / barCont.clientWidth;
    audio.currentTime = pct * audio.duration;
  });

  init3D();
});

let scene, camera, renderer, geometry, mesh, context, analyser, dataArray;
let clock, bloomComposer;
let mouseX = 0,
  mouseY = 0;

const params = {
  red: 1.0,
  green: 0.6,
  blue: 0.1,
  threshold: 0.5,
  strength: 0.5,
  radius: 0.8,
};

const vertexShader = `
  uniform float u_time;
  uniform float u_frequency;

  vec3 mod289(vec3 x) {
    return x - floor(x * (1.0 / 289.0)) * 289.0;
  }

  vec4 mod289(vec4 x) {
    return x - floor(x * (1.0 / 289.0)) * 289.0;
  }

  vec4 permute(vec4 x) {
    return mod289(((x*34.0)+10.0)*x);
  }

  vec4 taylorInvSqrt(vec4 r) {
    return 1.79284291400159 - 0.85373472095314 * r;
  }

  vec3 fade(vec3 t) {
    return t*t*t*(t*(t*6.0-15.0)+10.0);
  }

  float pnoise(vec3 P, vec3 rep) {
    vec3 Pi0 = mod(floor(P), rep);
    vec3 Pi1 = mod(Pi0 + vec3(1.0), rep);
    Pi0 = mod289(Pi0);
    Pi1 = mod289(Pi1);
    vec3 Pf0 = fract(P);
    vec3 Pf1 = Pf0 - vec3(1.0);
    vec4 ix = vec4(Pi0.x, Pi1.x, Pi0.x, Pi1.x);
    vec4 iy = vec4(Pi0.yy, Pi1.yy);
    vec4 iz0 = Pi0.zzzz;
    vec4 iz1 = Pi1.zzzz;

    vec4 ixy = permute(permute(ix) + iy);
    vec4 ixy0 = permute(ixy + iz0);
    vec4 ixy1 = permute(ixy + iz1);

    vec4 gx0 = ixy0 * (1.0 / 7.0);
    vec4 gy0 = fract(floor(gx0) * (1.0 / 7.0)) - 0.5;
    gx0 = fract(gx0);
    vec4 gz0 = vec4(0.5) - abs(gx0) - abs(gy0);
    vec4 sz0 = step(gz0, vec4(0.0));
    gx0 -= sz0 * (step(0.0, gx0) - 0.5);
    gy0 -= sz0 * (step(0.0, gy0) - 0.5);

    vec4 gx1 = ixy1 * (1.0 / 7.0);
    vec4 gy1 = fract(floor(gx1) * (1.0 / 7.0)) - 0.5;
    gx1 = fract(gx1);
    vec4 gz1 = vec4(0.5) - abs(gx1) - abs(gy1);
    vec4 sz1 = step(gz1, vec4(0.0));
    gx1 -= sz1 * (step(0.0, gx1) - 0.5);
    gy1 -= sz1 * (step(0.0, gy1) - 0.5);

    vec3 g000 = vec3(gx0.x,gy0.x,gz0.x);
    vec3 g100 = vec3(gx0.y,gy0.y,gz0.y);
    vec3 g010 = vec3(gx0.z,gy0.z,gz0.z);
    vec3 g110 = vec3(gx0.w,gy0.w,gz0.w);
    vec3 g001 = vec3(gx1.x,gy1.x,gz1.x);
    vec3 g101 = vec3(gx1.y,gy1.y,gz1.y);
    vec3 g011 = vec3(gx1.z,gy1.z,gz1.z);
    vec3 g111 = vec3(gx1.w,gy1.w,gz1.w);

    vec4 norm0 = taylorInvSqrt(vec4(dot(g000, g000), dot(g010, g010), dot(g100, g100), dot(g110, g110)));
    g000 *= norm0.x;
    g010 *= norm0.y;
    g100 *= norm0.z;
    g110 *= norm0.w;
    vec4 norm1 = taylorInvSqrt(vec4(dot(g001, g001), dot(g011, g011), dot(g101, g101), dot(g111, g111)));
    g001 *= norm1.x;
    g011 *= norm1.y;
    g101 *= norm1.z;
    g111 *= norm1.w;

    float n000 = dot(g000, Pf0);
    float n100 = dot(g100, vec3(Pf1.x, Pf0.yz));
    float n010 = dot(g010, vec3(Pf0.x, Pf1.y, Pf0.z));
    float n110 = dot(g110, vec3(Pf1.xy, Pf0.z));
    float n001 = dot(g001, vec3(Pf0.xy, Pf1.z));
    float n101 = dot(g101, vec3(Pf1.x, Pf0.y, Pf1.z));
    float n011 = dot(g011, vec3(Pf0.x, Pf1.yz));
    float n111 = dot(g111, Pf1);

    vec3 fade_xyz = fade(Pf0);
    vec4 n_z = mix(vec4(n000, n100, n010, n110), vec4(n001, n101, n011, n111), fade_xyz.z);
    vec2 n_yz = mix(n_z.xy, n_z.zw, fade_xyz.y);
    float n_xyz = mix(n_yz.x, n_yz.y, fade_xyz.x);
    return 2.2 * n_xyz;
  }

  void main() {
    float noise = 3.0 * pnoise(position + u_time, vec3(10.0));
    float displacement = (u_frequency / 30.0) * (noise / 10.0);
    vec3 newPosition = position + normal * displacement;
    gl_Position = projectionMatrix * modelViewMatrix * vec4(newPosition, 1.0);
  }
`;

const fragmentShader = `
  uniform float u_red;
  uniform float u_blue;
  uniform float u_green;

  void main() {
    gl_FragColor = vec4(vec3(u_red, u_green, u_blue), 1.0);
  }
`;

function init3D() {
  scene = new THREE.Scene();
  camera = new THREE.PerspectiveCamera(
    45,
    window.innerWidth / window.innerHeight,
    0.1,
    1000,
  );
  camera.position.set(0, -2, 14);
  camera.lookAt(0, 0, 0);

  renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.outputEncoding = THREE.sRGBEncoding;
  const container = document.getElementById("canvas-container");
  if (container) container.appendChild(renderer.domElement);

  const renderScene = new RenderPass(scene, camera);

  const bloomPass = new UnrealBloomPass(
    new THREE.Vector2(window.innerWidth, window.innerHeight),
  );
  bloomPass.threshold = params.threshold;
  bloomPass.strength = params.strength;
  bloomPass.radius = params.radius;

  bloomComposer = new EffectComposer(renderer);
  bloomComposer.addPass(renderScene);
  bloomComposer.addPass(bloomPass);

  const outputPass = new OutputPass();
  bloomComposer.addPass(outputPass);

  const uniforms = {
    u_time: { type: "f", value: 0.0 },
    u_frequency: { type: "f", value: 0.0 },
    u_red: { type: "f", value: params.red },
    u_green: { type: "f", value: params.green },
    u_blue: { type: "f", value: params.blue },
  };

  const mat = new THREE.ShaderMaterial({
    uniforms,
    vertexShader,
    fragmentShader,
  });

  geometry = new THREE.IcosahedronGeometry(4, 30);
  mesh = new THREE.Mesh(geometry, mat);
  mesh.material.wireframe = true;
  scene.add(mesh);

  clock = new THREE.Clock();

  document.addEventListener("mousemove", (e) => {
    const windowHalfX = window.innerWidth / 2;
    const windowHalfY = window.innerHeight / 2;
    mouseX = (e.clientX - windowHalfX) / 100;
    mouseY = (e.clientY - windowHalfY) / 100;
  });

  animate();
}

function initAudioContext() {
  if (context) return;
  const audio = document.getElementById("audio");
  context = new window.AudioContext();
  analyser = context.createAnalyser();
  const src = context.createMediaElementSource(audio);
  src.connect(analyser);
  analyser.connect(context.destination);
  analyser.fftSize = 32;
  dataArray = new Uint8Array(analyser.frequencyBinCount);
}

function animate() {
  requestAnimationFrame(animate);

  camera.position.x += (mouseX - camera.position.x) * 0.05;
  camera.position.y += (-mouseY - camera.position.y) * 0.5;
  camera.lookAt(scene.position);

  if (mesh && mesh.material.uniforms) {
    mesh.material.uniforms.u_time.value = clock.getElapsedTime();

    if (analyser && dataArray) {
      analyser.getByteFrequencyData(dataArray);
      const averageFrequency =
        dataArray.reduce((a, b) => a + b, 0) / dataArray.length;
      mesh.material.uniforms.u_frequency.value = averageFrequency;
    }
  }

  bloomComposer.render();
}

window.addEventListener("resize", () => {
  if (camera && renderer) {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
    if (bloomComposer) {
      bloomComposer.setSize(window.innerWidth, window.innerHeight);
    }
  }
});
