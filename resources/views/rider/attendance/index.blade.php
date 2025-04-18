{{-- resources/views/rider/attendance/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Check-in / Check-out') }}
        </h2>
    </x-slot>

    @push('styles')
        {{-- Basic styling for scanner/camera elements --}}
        <style>
            #qr-reader { width: 100%; max-width: 500px; margin: 1rem auto; border: 1px solid #ccc; }
            #video-preview { display: none; max-width: 100%; margin-top: 1rem; border: 1px solid #ccc; }
            #photo-canvas { display: none; } /* Hidden canvas for capturing frame */
            #capture-button, #submit-checkin { display: none; } /* Show only when needed */
            .scanner-loading::after { content: 'Loading Scanner...'; display: block; padding: 20px; text-align: center; }
            #status-message { margin-top: 1rem; padding: 0.5rem; border-radius: 4px; text-align: center; }
            .status-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .status-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        </style>
    @endpush

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div id="status-message" role="alert" style="display: none;"></div>

                    {{-- Display current status --}}
                    @if($isCheckedIn)
                         <div class="alert alert-info mb-4">
                            You are currently checked in since: {{ $currentAttendance->check_in->format('M d, Y - h:i A') }}
                         </div>
                         {{-- TODO: Add Check-out Button/Form here --}}
                         <button class="btn btn-danger w-full" disabled>Check-out (Not Implemented)</button>
                    @else
                         <div class="alert alert-warning mb-4">
                            You are currently checked out. Please scan the QR code below to check in.
                         </div>

                         {{-- 1. QR Code Scanner Area --}}
                         <p class="text-center font-medium mb-2">Scan Admin QR Code:</p>
                         <div id="qr-reader" class="scanner-loading"></div>
                         <div id="qr-reader-results" class="text-center text-sm text-gray-500 mt-1"></div>

                         {{-- 2. Video/Photo Area (Initially Hidden) --}}
                         <div id="camera-section" style="display: none;" class="mt-6 text-center">
                             <p class="font-medium mb-2">Take Verification Photo:</p>
                             <video id="video-preview" autoplay playsinline></video>
                             <canvas id="photo-canvas"></canvas> {{-- Hidden canvas --}}
                             <button id="capture-button" class="btn btn-secondary mt-2">Capture Photo</button>
                         </div>

                         {{-- 3. Hidden Form for Submission --}}
                         <form id="checkin-form" style="display: none;" class="mt-4">
                             @csrf
                             <input type="hidden" name="qr_code_data" id="qr_code_data">
                             <input type="hidden" name="photo_blob" id="photo_blob"> {{-- We'll send blob via FormData --}}
                             <img id="captured-photo-preview" src="" alt="Captured Photo" class="mx-auto mb-2 border" style="max-width: 200px; display: none;">
                             <button id="submit-checkin" type="submit" class="btn btn-primary w-full">Submit Check-in</button>
                         </form>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- QR Scanner Library --}}
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

        <script>
             // --- State Variables ---
            let html5QrCode = null;
            let stream = null;
            let capturedBlob = null;
            let scannedQrData = null;

            // --- DOM Elements ---
            const qrReaderElement = document.getElementById('qr-reader');
            const resultsElement = document.getElementById('qr-reader-results');
            const cameraSection = document.getElementById('camera-section');
            const videoPreview = document.getElementById('video-preview');
            const captureButton = document.getElementById('capture-button');
            const photoCanvas = document.getElementById('photo-canvas');
            const checkinForm = document.getElementById('checkin-form');
            const submitButton = document.getElementById('submit-checkin');
            const qrCodeDataInput = document.getElementById('qr_code_data');
            const capturedPhotoPreview = document.getElementById('captured-photo-preview');
            const statusMessage = document.getElementById('status-message');

            // --- Functions ---
            function showStatus(message, isSuccess) {
                statusMessage.textContent = message;
                statusMessage.className = isSuccess ? 'status-success' : 'status-error';
                statusMessage.style.display = 'block';
                // Auto-hide after 5 seconds
                setTimeout(() => { statusMessage.style.display = 'none'; }, 5000);
            }

            function stopCameraStream() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                    videoPreview.srcObject = null;
                    videoPreview.style.display = 'none';
                    captureButton.style.display = 'none';
                }
            }

            function stopScanner() {
                if (html5QrCode && html5QrCode.isScanning) {
                    html5QrCode.stop().catch(err => console.error("Error stopping scanner:", err));
                }
                stopCameraStream(); // Also stop camera if it was opened by scanner
            }

            // QR Success Callback
            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                resultsElement.textContent = `QR Code Scanned: ${decodedText}`;
                scannedQrData = decodedText;
                qrCodeDataInput.value = decodedText; // Set hidden input

                // Stop the scanner
                stopScanner();
                qrReaderElement.style.display = 'none'; // Hide scanner element

                // Initiate camera
                startCamera();
            };

            // QR Error Callback (Optional)
            const qrCodeErrorCallback = (errorMessage) => {
                 // console.warn(`QR Code scan error: ${errorMessage}`);
            };

            // Start Camera Function
            function startCamera() {
                 cameraSection.style.display = 'block';
                 videoPreview.style.display = 'block';
                 captureButton.style.display = 'inline-block'; // Show capture button
                 checkinForm.style.display = 'none'; // Hide form initially
                 capturedPhotoPreview.style.display = 'none';

                 navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } }) // Use front camera
                    .then(mediaStream => {
                        stream = mediaStream;
                        videoPreview.srcObject = stream;
                    })
                    .catch(err => {
                        console.error("Error accessing camera:", err);
                        showStatus("Could not access camera. Please grant permission.", false);
                        cameraSection.style.display = 'none'; // Hide section if camera fails
                        // Optionally restart QR scanner here
                        // startScanner();
                    });
            }

            // Capture Photo Function
            captureButton.addEventListener('click', () => {
                if (!stream) return;

                const context = photoCanvas.getContext('2d');
                // Set canvas dimensions to match video element to maintain aspect ratio
                photoCanvas.width = videoPreview.videoWidth;
                photoCanvas.height = videoPreview.videoHeight;

                // Draw current video frame onto the canvas
                context.drawImage(videoPreview, 0, 0, photoCanvas.width, photoCanvas.height);

                // Get image data from canvas as Blob
                photoCanvas.toBlob(blob => {
                     capturedBlob = blob;
                     if (capturedBlob) {
                        // Show preview (optional)
                        const reader = new FileReader();
                        reader.onloadend = () => {
                            capturedPhotoPreview.src = reader.result;
                            capturedPhotoPreview.style.display = 'block';
                        }
                        reader.readAsDataURL(capturedBlob);

                         // Hide camera, show form
                         stopCameraStream();
                         cameraSection.style.display = 'none';
                         checkinForm.style.display = 'block';
                         submitButton.style.display = 'inline-block';
                     } else {
                         showStatus("Failed to capture photo.", false);
                     }
                }, 'image/jpeg', 0.9); // Save as JPEG with 90% quality
            });

             // Handle Form Submission (AJAX)
            checkinForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (!scannedQrData || !capturedBlob) {
                    showStatus("Missing QR data or photo.", false);
                    return;
                }

                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';

                const formData = new FormData();
                formData.append('qr_code_data', scannedQrData);
                formData.append('photo', capturedBlob, 'attendance_photo.jpg'); // Append blob as a file
                formData.append('_token', document.querySelector('input[name="_token"]').value); // CSRF token

                try {
                    const response = await fetch('{{ route("rider.attendance.checkin") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json', // Expect JSON response
                            'X-Requested-With': 'XMLHttpRequest',
                            // CSRF token included in FormData, but good practice to have meta tag too
                        }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        showStatus(result.message, true);
                        // Optionally redirect or update UI to show checked-in status
                         window.location.reload(); // Simple reload for now
                    } else {
                         showStatus(result.message || 'Check-in failed. Please try again.', false);
                    }
                } catch (error) {
                    console.error('Check-in submission error:', error);
                    showStatus('An network error occurred. Please try again.', false);
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Check-in';
                    // Reset state if needed
                    // capturedBlob = null;
                    // scannedQrData = null;
                    // checkinForm.style.display = 'none';
                    // capturedPhotoPreview.style.display = 'none';
                    // startScanner(); // Restart scanner?
                }
            });


            // --- Initialization ---
            @if(!$isCheckedIn) // Only start scanner if not checked in
                // Check for camera permissions before starting scanner fully
                navigator.mediaDevices.enumerateDevices()
                    .then(devices => {
                         const hasCamera = devices.some(device => device.kind === 'videoinput');
                         if (hasCamera) {
                              html5QrCode = new Html5Qrcode("qr-reader");
                              const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                              qrReaderElement.classList.remove('scanner-loading');
                              html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback, qrCodeErrorCallback)
                                .catch(err => {
                                    console.error("Error starting QR scanner:", err);
                                    showStatus("Could not start QR scanner.", false);
                                    qrReaderElement.textContent = 'QR Scanner failed to start.';
                                });
                         } else {
                              qrReaderElement.textContent = 'No camera found for scanning.';
                              showStatus("No camera found.", false);
                         }
                    })
                    .catch(err => {
                         console.error("Error enumerating devices:", err);
                         qrReaderElement.textContent = 'Error accessing media devices.';
                         showStatus("Could not access media devices.", false);
                    });


                // Cleanup on page leave
                window.addEventListener('beforeunload', () => {
                    stopScanner();
                    stopCameraStream();
                });
            @endif

        </script>
    @endpush

</x-app-layout>