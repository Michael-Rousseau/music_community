<?php
namespace Controllers;

class MusicController {
    public function show($id) {
        echo "<h1>Music Page #$id</h1>";
        echo "<p>Music details will appear here.</p>";
    }

    public function create() {
        echo "<h1>Upload a new music</h1>";
        echo "<form method='post' enctype='multipart/form-data'>
                <input type='text' name='title' placeholder='Title'><br>
                <textarea name='description' placeholder='Description'></textarea><br>
                <input type='file' name='mp3'><br>
                <button type='submit'>Upload</button>
              </form>";
    }

    public function store() {
        // Placeholder for storing uploaded music
        echo "<p>Music uploaded successfully! Redirect to /m/new/success</p>";
    }

    public function success() {
        echo "<h1>Music uploaded successfully!</h1>";
    }
}
