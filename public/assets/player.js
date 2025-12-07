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
let simplex;
let smoothedBass = 0;
let smoothedMid = 0;
let smoothedTreble = 0;

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

  // Softer, more balanced lighting with multiple colors
  const l1 = new THREE.PointLight(0x4a90e2, 1.5); // Blue
  l1.position.set(5, 5, 5);
  scene.add(l1);
  const l2 = new THREE.PointLight(0x9b59b6, 1.5); // Purple
  l2.position.set(-5, -5, 5);
  scene.add(l2);
  const l3 = new THREE.PointLight(0xe74c3c, 1); // Red
  l3.position.set(0, 5, -5);
  scene.add(l3);
  const ambientLight = new THREE.AmbientLight(0x202020, 2);
  scene.add(ambientLight);

  // Create blob with high subdivisions for ultra-smooth deformation
  geometry = new THREE.SphereGeometry(2, 128, 128);

  // Store original positions for smooth interpolation
  const originalPositions = new Float32Array(geometry.attributes.position.array);
  geometry.userData.originalPositions = originalPositions;

  const mat = new THREE.MeshPhysicalMaterial({
    color: 0x6b7fd7, // Softer blue-purple
    roughness: 0.2,
    metalness: 0.1,
    clearcoat: 0.8,
    clearcoatRoughness: 0.2,
    transparent: true,
    opacity: 0.9,
    wireframe: false,
    flatShading: false,
  });

  mesh = new THREE.Mesh(geometry, mat);
  scene.add(mesh);

  // Initialize simplex noise for organic blob movement
  simplex = new SimplexNoise();

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

  // Extract audio frequency data
  let bass = 0;
  let mid = 0;
  let treble = 0;
  let overall = 0;

  if (analyser && dataArray) {
    analyser.getByteFrequencyData(dataArray);
    const third = Math.floor(dataArray.length / 3);

    // Split frequencies into bass, mid, and treble
    const bassArray = dataArray.slice(0, third);
    const midArray = dataArray.slice(third, third * 2);
    const trebleArray = dataArray.slice(third * 2);

    bass = bassArray.reduce((a, b) => a + b, 0) / bassArray.length / 255;
    mid = midArray.reduce((a, b) => a + b, 0) / midArray.length / 255;
    treble = trebleArray.reduce((a, b) => a + b, 0) / trebleArray.length / 255;
    overall = dataArray.reduce((a, b) => a + b, 0) / dataArray.length / 255;

    // Smooth the audio values for cleaner movement
    const smoothing = 0.85;
    smoothedBass += (bass - smoothedBass) * (1 - smoothing);
    smoothedMid += (mid - smoothedMid) * (1 - smoothing);
    smoothedTreble += (treble - smoothedTreble) * (1 - smoothing);
  }

  if (geometry && simplex) {
    const pos = geometry.attributes.position;
    const originalPos = geometry.userData.originalPositions;
    const time = performance.now() * 0.0002; // Slower base movement

    for (let i = 0; i < pos.count; i++) {
      const i3 = i * 3;

      // Get original position
      const ox = originalPos[i3];
      const oy = originalPos[i3 + 1];
      const oz = originalPos[i3 + 2];

      // Normalize for direction
      const length = Math.sqrt(ox * ox + oy * oy + oz * oz);
      const nx = ox / length;
      const ny = oy / length;
      const nz = oz / length;

      // Single smooth noise layer for cleaner movement
      const noise = simplex.noise3D(
        nx * 0.8 + time,
        ny * 0.8 + time,
        nz * 0.8 + time
      );

      // Subtle audio reactivity using smoothed values
      const audioDisplacement =
        smoothedBass * 0.4 +                    // Gentle bass pulsing
        smoothedMid * 0.3 * (noise * 0.5 + 0.5) +  // Subtle mid variation
        smoothedTreble * 0.1;                   // Minimal treble shimmer

      // Calculate final displacement with reduced noise influence
      const baseRadius = 2.0;
      const displacement = noise * 0.25 + audioDisplacement;
      const finalRadius = baseRadius + displacement;

      // Apply to position
      pos.setXYZ(i, nx * finalRadius, ny * finalRadius, nz * finalRadius);
    }

    geometry.computeVertexNormals();
    pos.needsUpdate = true;

    // Minimal rotation for cleaner look
    mesh.rotation.y += 0.0005 + overall * 0.001;
    mesh.rotation.x += 0.0002;
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
