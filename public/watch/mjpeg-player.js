const canvas = document.getElementById('player');
const ctx = canvas.getContext('2d');

fetch(MJPEG_FILE_URL)
  .then(res => res.arrayBuffer())
  .then(buffer => {
    const bytes = new Uint8Array(buffer);
    let i = 0;

    function nextFrame() {
        // Find start (0xFFD8) and end (0xFFD9) of JPEG
        let start = -1, end = -1;
        for (let j = i; j < bytes.length - 1; j++) {
            if (bytes[j] === 0xFF && bytes[j+1] === 0xD8) start = j;
            if (bytes[j] === 0xFF && bytes[j+1] === 0xD9 && start !== -1) {
                end = j + 2;
                break;
            }
        }
        if (start === -1 || end === -1) return;

        const blob = new Blob([bytes.slice(start, end)], { type: 'image/jpeg' });
        const img = new Image();
        img.onload = () => {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            i = end;
            requestAnimationFrame(nextFrame);
        };
        img.src = URL.createObjectURL(blob);
    }

    nextFrame();
  })
  .catch(err => {
    console.error('Failed to load MJPEG file:', err);
  });
