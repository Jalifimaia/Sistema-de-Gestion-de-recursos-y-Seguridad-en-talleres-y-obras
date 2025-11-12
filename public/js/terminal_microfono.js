
const microfono_flotante = document.getElementById('microfono_flotante');
const umbral_sonido = 10;

async function startVAD() {
    try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    const audioContext = new AudioContext();
    const source = audioContext.createMediaStreamSource(stream);
    const analyser = audioContext.createAnalyser();
    analyser.fftSize = 512;

    const dataArray = new Uint8Array(analyser.frequencyBinCount);
    source.connect(analyser);

    let pulsing = false;

    function detectVoice() {
        analyser.getByteFrequencyData(dataArray);
        const volume = dataArray.reduce((a, b) => a + b, 0) / dataArray.length;

        if (volume > umbral_sonido) {
        if (!pulsing) {
            microfono_flotante.classList.add('pulsing');
            pulsing = true;
        }
        } else {
        if (pulsing) {
            microfono_flotante.classList.remove('pulsing');
            pulsing = false;
        }
        }

        requestAnimationFrame(detectVoice);
    }

    detectVoice();
    } catch (err) {
    console.error('Error accediendo al micrófono:', err);
    alert('No se pudo acceder al micrófono.');
    }
}

startVAD();
