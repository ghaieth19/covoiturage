<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Haking Face Scan</title>
    <script defer src="https://unpkg.com/face-api.js"></script>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #222;
            color: #fff;
            margin-top: 40px;
        }
        video, canvas {
            border: 2px solid #fff;
            border-radius: 10px;
        }
        .message {
            margin-top: 20px;
            font-size: 18px;
        }
    </style>
</head>
<body>

    <h1>Scan Facial - Haking Face</h1>
    <video id="video" width="320" height="240" autoplay muted></video>
    <canvas id="canvas" width="320" height="240" style="display: none;"></canvas>
    <div class="message" id="message">Chargement...</div>

    <script>
        const video = document.getElementById('video');
        const message = document.getElementById('message');

        // Chargement des modèles depuis le dossier 'model'
        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/model'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/model'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/model')
        ]).then(startVideo);

        function startVideo() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => video.srcObject = stream)
                .catch(err => message.textContent = "Accès à la webcam refusé.");
        }

        video.addEventListener('play', async () => {
            message.textContent = "Analyse en cours...";

            const labeledDescriptors = await loadLabeledImages();
            const faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);

            const canvas = faceapi.createCanvasFromMedia(video);
            document.body.append(canvas);

            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);

            setInterval(async () => {
                const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                faceapi.draw.drawDetections(canvas, resizedDetections);

                if (resizedDetections.length > 0) {
                    const result = faceMatcher.findBestMatch(resizedDetections[0].descriptor);
                    if (result.label !== "inconnu") {
                        message.textContent = "Bienvenue, accès autorisé !";
                        setTimeout(() => window.location.href = 'interface.php', 2000);
                    } else {
                        message.textContent = "Visage non reconnu.";
                    }
                }
            }, 1000);
        });

        function loadLabeledImages() {
            const labels = ['123456']; // Remplacez avec des identifiants de vos utilisateurs
            return Promise.all(
                labels.map(async label => {
                    const imgUrl = `/images/users/${label}.jpg`; // Assurez-vous que les images existent
                    const img = await faceapi.fetchImage(imgUrl);
                    const detection = await faceapi
                        .detectSingleFace(img)
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    if (!detection) throw new Error("Aucune détection sur l'image de référence.");
                    return new faceapi.LabeledFaceDescriptors(label, [detection.descriptor]);
                })
            );
        }
    </script>

</body>
</html>
