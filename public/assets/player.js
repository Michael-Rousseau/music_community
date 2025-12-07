function toggleDrawer(show) {
  const d = document.getElementById("drawer");
  const o = document.getElementById("overlay");
  if (show) {
    d.classList.add("open");
    o.classList.add("visible");
  } else {
    d.classList.remove("open");
    o.classList.remove("visible");
  }
}

function submitRating(val) {
  if (!isUserLoggedIn) {
    window.location.href = "connexion.php";
  } else {
    document.getElementById("ratingInput").value = val;
    document.getElementById("ratingForm").submit();
  }
}

function jumpTo(seconds) {
  const audio = document.getElementById("audio");
  audio.currentTime = seconds;
  if (audio.paused) {
    document.getElementById("playBtn").click();
  }
}

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

function init3D() {
  scene = new THREE.Scene();
  camera = new THREE.PerspectiveCamera(
    75,
    window.innerWidth / window.innerHeight,
    0.1,
    1000,
  );
  camera.position.z = 5;

  renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  const container = document.getElementById("canvas-container");
  if (container) container.appendChild(renderer.domElement);

  const l1 = new THREE.PointLight(0xbd00ff, 1);
  l1.position.set(5, 5, 5);
  scene.add(l1);
  const l2 = new THREE.PointLight(0x00f3ff, 1);
  l2.position.set(-5, -5, 5);
  scene.add(l2);
  const ambientLight = new THREE.AmbientLight(0x404040);
  scene.add(ambientLight);

  geometry = new THREE.IcosahedronGeometry(2, 10);
  const mat = new THREE.MeshStandardMaterial({
    color: 0xbd00ff,
    roughness: 0.2,
    metalness: 0.8,
    wireframe: false,
  });

  mesh = new THREE.Mesh(geometry, mat);
  scene.add(mesh);

  animate();
}

function initAudioContext() {
  if (context) return;
  const audio = document.getElementById("audio");
  context = new (window.AudioContext || window.webkitAudioContext)();
  analyser = context.createAnalyser();
  const src = context.createMediaElementSource(audio);
  src.connect(analyser);
  analyser.connect(context.destination);
  analyser.fftSize = 512;
  dataArray = new Uint8Array(analyser.frequencyBinCount);
}

function animate() {
  requestAnimationFrame(animate);

  let bass = 0;
  if (analyser) {
    analyser.getByteFrequencyData(dataArray);
    let lowerHalf = dataArray.slice(0, dataArray.length / 2 - 1);
    bass = lowerHalf.reduce((a, b) => Math.max(a, b)) / 255;
  }

  if (geometry) {
    const pos = geometry.attributes.position;
    const vec = new THREE.Vector3();
    const time = performance.now() * 0.002;

    for (let i = 0; i < pos.count; i++) {
      vec.fromBufferAttribute(pos, i);
      vec.normalize();
      const distance =
        2 +
        bass *
          1.5 *
          (Math.sin(vec.x + time) +
            Math.sin(vec.y + time) +
            Math.sin(vec.z + time));
      vec.multiplyScalar(distance);
      pos.setXYZ(i, vec.x, vec.y, vec.z);
    }

    geometry.computeVertexNormals();
    pos.needsUpdate = true;

    mesh.rotation.y += 0.002;
    mesh.rotation.x += 0.001;
  }

  renderer.render(scene, camera);
}

window.addEventListener("resize", () => {
  if (camera && renderer) {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
  }
});
