<!-- Persistent Music Player -->
<div id="persistent-player" class="persistent-player-container" style="display: none;">
    <div class="persistent-player-bar">
        <button id="playBtn" class="play-btn" aria-label="Play Music">
            <i class="fas fa-play"></i>
        </button>

        <div class="progress-wrapper" id="progressContainer">
            <div class="progress-fill" id="progressBar"></div>
        </div>

        <div class="time" style="font-family:monospace; font-weight:bold; color:var(--text-main); margin-right:15px;">
            <span id="currTime">00:00</span>
        </div>

        <div class="song-info-mini">
            <div id="currentSongTitle" style="font-weight:700; color:var(--text-main); font-size:0.95rem;">
                No song loaded
            </div>
            <div id="currentArtist" style="font-size:0.85rem; color:var(--text-muted);">
            </div>
        </div>
    </div>
</div>

<!-- Global Audio Element -->
<audio id="audio" crossorigin="anonymous"></audio>

<style>
/* persistent player styles - floating overlay */
.persistent-player-container {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    background: var(--bg-card);
    border: 2px solid var(--primary);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    padding: 15px 30px;
    max-width: 800px;
    width: calc(100% - 40px);
    backdrop-filter: blur(10px);
    background: rgba(var(--bg-card-rgb, 249, 249, 249), 0.95);
}

body.dark-mode .persistent-player-container {
    background: rgba(30, 30, 30, 0.95);
}

.persistent-player-bar {
    display: flex;
    align-items: center;
    gap: 15px;
}

.play-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--primary);
    color: #2D2828;
    border: none;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
    flex-shrink: 0;
}

.play-btn:hover {
    transform: scale(1.1);
}

.progress-wrapper {
    flex: 1;
    height: 8px;
    background: var(--bg-input);
    border-radius: 10px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 10px;
    width: 0%;
    transition: width 0.1s linear;
}

.song-info-mini {
    display: flex;
    flex-direction: column;
    min-width: 200px;
    max-width: 300px;
}

#currentSongTitle {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (max-width: 768px) {
    .persistent-player-container {
        padding: 10px 15px;
    }

    .persistent-player-bar {
        gap: 10px;
    }

    .song-info-mini {
        min-width: 0;
        max-width: 150px;
    }

    .time {
        margin-right: 5px !important;
    }
}
</style>

<script>
// player controls - will be enhanced by player-state.js
document.addEventListener("DOMContentLoaded", () => {
    const audio = document.getElementById("audio");
    const playBtn = document.getElementById("playBtn");
    const progressBar = document.getElementById("progressBar");
    const progressContainer = document.getElementById("progressContainer");
    const currTime = document.getElementById("currTime");

    if (!audio || !playBtn) return;

    // play/pause button
    playBtn.addEventListener("click", () => {
        if (audio.paused) {
            audio.play();
        } else {
            audio.pause();
        }
    });

    // update button icon based on state
    audio.addEventListener("play", () => {
        playBtn.innerHTML = '<i class="fas fa-pause"></i>';
    });

    audio.addEventListener("pause", () => {
        playBtn.innerHTML = '<i class="fas fa-play"></i>';
    });

    // update progress bar
    audio.addEventListener("timeupdate", () => {
        if (audio.duration) {
            const percent = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = percent + "%";

            // update time display
            const mins = Math.floor(audio.currentTime / 60);
            const secs = Math.floor(audio.currentTime % 60);
            currTime.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
    });

    // click to seek
    progressContainer.addEventListener("click", (e) => {
        if (!audio.duration) return;
        const rect = progressContainer.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const percent = x / rect.width;
        audio.currentTime = percent * audio.duration;
    });
});
</script>
