<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Viewer</title>
    <style>
        body { margin: 0; overflow: hidden; display: flex; flex-direction: column; align-items: center; }
        canvas { display: block; }
    </style>
</head>
<body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
    let scene, camera, renderer, plane, material, texture;
    let images = [];
    let isTouchActive = false; // Track whether a touch interaction is active
    let isMouseMoveActive = true; // Track whether the mouse movement should control the image
    const autoMoveSpeed = 0.1; // Sensitivity for mouse movement

    <?php
    $jsonPath = "data/" . $_GET["dataset"] . "/images.json";
    $imageNames = file_get_contents($jsonPath);
    $dragSensitivity = isset($_GET["dragSensitivity"]) ? floatval($_GET["dragSensitivity"]) : 0.2;
    $startIndex = isset($_GET["startIndex"]) ? intval($_GET["startIndex"]) : 0;
    ?>
    const imageFilenames = <?php echo $imageNames; ?>;
    const datasetPath = `data/<?php echo $_GET["dataset"]; ?>/`;
    const dragSensitivity = <?php echo $dragSensitivity; ?>;
    let currentImageIndex = <?php echo $startIndex; ?>;

    function preloadImages() {
        if (!imageFilenames || imageFilenames.length === 0) {
            console.error("No images found or dataset not specified.");
            return;
        }

        images = new Array(imageFilenames.length);
        let loadedImages = 0;

        imageFilenames.forEach((filename, index) => {
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.src = `${datasetPath}${filename}`;
            img.onload = () => {
                images[index] = { element: img, name: filename };
                loadedImages++;
                if (index === 0) { // Adjust geometry based on the first image's aspect ratio
                    adjustGeometry(img.width, img.height);
                }
                if (loadedImages === imageFilenames.length) {
                    displayFirstImage(); // Display the first image immediately
                    startRendering(); // Start rendering only after all images are loaded
                }
            };
        });
    }

    function adjustGeometry(width, height) {
        const aspect = width / height;
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        let planeWidth, planeHeight;

        if (viewportWidth / viewportHeight > aspect) {
            // Viewport is wider than the image's aspect ratio
            planeHeight = viewportHeight;
            planeWidth = planeHeight * aspect;
        } else {
            // Viewport is narrower or equal to the image's aspect ratio
            planeWidth = viewportWidth;
            planeHeight = planeWidth / aspect;
        }

        if (plane) {
            scene.remove(plane);
            plane.geometry.dispose();
        }

        const geometry = new THREE.PlaneGeometry(planeWidth, planeHeight);
        plane = new THREE.Mesh(geometry, material);
        scene.add(plane);

        // Adjust camera distance to fit the plane within the viewport
        const distance = (planeHeight / 2) / Math.tan(THREE.MathUtils.degToRad(camera.fov / 2));
        camera.position.z = distance;
        camera.updateProjectionMatrix();
    }

    function displayFirstImage() {
        if (images.length > 0 && images[currentImageIndex]?.element.complete) {
            texture.image = images[currentImageIndex].element;
            texture.needsUpdate = true;
        }
    }

    function startRendering() {
        const canvas = renderer.domElement;

        // Mouse move event for desktop (only if touch is not active)
        canvas.addEventListener('mousemove', (event) => {
            if (isMouseMoveActive && !isTouchActive && images.length > 0) {
                const mouseX = event.clientX;
                const fraction = 1 - (mouseX / window.innerWidth); // Invert movement by subtracting from 1
                const totalImages = images.length;
                const newIndex = Math.floor(fraction * totalImages); // Map fraction to an image index

                // Update the image if the index has changed
                if (newIndex !== currentImageIndex) {
                    currentImageIndex = newIndex;
                    if (images[currentImageIndex].element.complete) {
                        texture.image = images[currentImageIndex].element;
                        texture.needsUpdate = true;
                    }
                }
            }
        });

        // Touch start event for mobile
        canvas.addEventListener('touchstart', (event) => {
            isTouchActive = true;
            isMouseMoveActive = false; // Disable mouse move-based control while touch is active
        });

        // Touch move event for mobile
        canvas.addEventListener('touchmove', (event) => {
            if (isTouchActive && images.length > 0 && event.touches.length > 0) {
                const touchX = event.touches[0].clientX;
                const fraction = 1 - (touchX / window.innerWidth); // Invert movement by subtracting from 1
                const totalImages = images.length;
                const newIndex = Math.floor(fraction * totalImages); // Map fraction to an image index

                // Update the image if the index has changed
                if (newIndex !== currentImageIndex) {
                    currentImageIndex = newIndex;
                    if (images[currentImageIndex].element.complete) {
                        texture.image = images[currentImageIndex].element;
                        texture.needsUpdate = true;
                    }
                }
            }
        });

        // Touch end event to re-enable mouse movement
        canvas.addEventListener('touchend', () => {
            isTouchActive = false;
            isMouseMoveActive = true; // Re-enable mouse move-based control after touch ends
        });

        animate();
    }

    function init() {
        scene = new THREE.Scene();

        camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);

        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.body.appendChild(renderer.domElement);

        texture = new THREE.Texture();
        material = new THREE.MeshBasicMaterial({ map: texture });
        plane = new THREE.Mesh(new THREE.PlaneGeometry(5, 5), material);
        scene.add(plane);

        window.addEventListener('resize', onWindowResize, false);
    }

    function onWindowResize() {
        if (images.length > 0) {
            adjustGeometry(images[currentImageIndex]?.element.width, images[currentImageIndex]?.element.height);
        }
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    }

    function animate() {
        requestAnimationFrame(animate);
        if (images.length > 0 && images[currentImageIndex]?.element.complete) {
            renderer.render(scene, camera);
        }
    }

    // Initialize and load the images
    window.onload = () => {
        init();
        preloadImages();
    };
</script>
</body>
</html>
