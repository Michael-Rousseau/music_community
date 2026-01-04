// Player state manager for persistent music playback
class PlayerState {
    constructor() {
        this.currentSong = null;
        this.isPlaying = false;
        this.currentTime = 0;
        this.duration = 0;
        this.loadFromStorage();
        this.initializePlayer();
    }

    // load saved state from localStorage
    loadFromStorage() {
        try {
            const saved = localStorage.getItem('playerState');
            if (saved) {
                const state = JSON.parse(saved);
                this.currentSong = state.currentSong;
                this.currentTime = state.currentTime || 0;
            }
        } catch (e) {
            console.error('failed to load player state:', e);
        }
    }

    // save current state to localStorage
    saveToStorage() {
        try {
            localStorage.setItem('playerState', JSON.stringify({
                currentSong: this.currentSong,
                currentTime: this.currentTime,
                isPlaying: this.isPlaying
            }));
        } catch (e) {
            console.error('failed to save player state:', e);
        }
    }

    // initialize player on page load
    initializePlayer() {
        document.addEventListener('DOMContentLoaded', () => {
            const audio = document.getElementById('audio');
            if (!audio) return;

            // restore previous song if exists
            if (this.currentSong) {
                this.updateAudioElement();
                this.showPlayer();
            }

            // save current time periodically
            audio.addEventListener('timeupdate', () => {
                this.currentTime = audio.currentTime;
                // debounce storage writes
                if (!this._saveTimeout) {
                    this._saveTimeout = setTimeout(() => {
                        this.saveToStorage();
                        this._saveTimeout = null;
                    }, 2000);
                }
            });

            // update duration
            audio.addEventListener('loadedmetadata', () => {
                this.duration = audio.duration;
            });

            // track play/pause state
            audio.addEventListener('play', () => {
                this.isPlaying = true;
                this.saveToStorage();
            });

            audio.addEventListener('pause', () => {
                this.isPlaying = false;
                this.saveToStorage();
            });
        });
    }

    // load a new song into player
    loadSong(musicId, title, artist) {
        this.currentSong = {
            id: musicId,
            title: title,
            artist: artist,
            streamUrl: `/music/stream?id=${musicId}`
        };
        this.currentTime = 0;
        this.saveToStorage();
        this.updateAudioElement();
        this.showPlayer();
        this.autoPlay();
    }

    // update the audio element with current song
    updateAudioElement() {
        const audio = document.getElementById('audio');
        if (!audio || !this.currentSong) return;

        // only change source if its different
        if (audio.src !== window.location.origin + this.currentSong.streamUrl) {
            audio.src = this.currentSong.streamUrl;
        }

        audio.currentTime = this.currentTime;

        // update UI elements
        const titleEl = document.getElementById('currentSongTitle');
        const artistEl = document.getElementById('currentArtist');

        if (titleEl) titleEl.textContent = this.currentSong.title;
        if (artistEl) artistEl.textContent = this.currentSong.artist;
    }

    // show the persistent player
    showPlayer() {
        const player = document.getElementById('persistent-player');
        if (player) {
            player.style.display = 'block';
        }
    }

    // hide the persistent player
    hidePlayer() {
        const player = document.getElementById('persistent-player');
        if (player) {
            player.style.display = 'none';
        }
    }

    // auto-play after loading
    autoPlay() {
        const audio = document.getElementById('audio');
        if (!audio) return;

        // small delay to ensure audio is ready
        setTimeout(() => {
            audio.play().catch(err => {
                console.log('autoplay prevented:', err);
                // browser blocked autoplay, user needs to click play
            });
        }, 100);
    }

    // toggle play/pause
    togglePlay() {
        const audio = document.getElementById('audio');
        if (!audio || !this.currentSong) return;

        if (this.isPlaying) {
            audio.pause();
        } else {
            audio.play();
        }
    }

    // get current song info
    getCurrentSong() {
        return this.currentSong;
    }
}

// create global instance
window.playerState = new PlayerState();

// helper function for loading songs from onclick attributes
window.loadSong = (id, title, artist) => {
    window.playerState.loadSong(id, title, artist);
};
