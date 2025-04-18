{{-- resources/views/admin/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    {{-- Include OpenStreetMap and OSRM styles --}}
    @push('styles')
        <style>
            #adminMap { 
                height: 600px; 
                width: 100%;
                position: relative;
            }
            .rider-marker {
                position: absolute;
                transform: translate(-50%, -50%);
                z-index: 10;
                text-align: center;
            }
            .rider-marker .status-indicator {
                display: block;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                margin: 0 auto 2px;
                border: 1px solid #fff;
                box-shadow: 0 0 3px rgba(0,0,0,0.5);
            }
            .rider-marker .rider-name {
                background: rgba(255,255,255,0.8);
                padding: 1px 3px;
                border-radius: 2px;
                font-size: 12px;
                font-weight: bold;
            }
            .status-available { background-color: #28a745; } /* Green */
            .status-on_task { background-color: #007bff; } /* Blue */
            .status-on_break { background-color: #ffc107; } /* Yellow */
            .status-offline { background-color: #6c757d; } /* Gray */

            .rider-popup {
                position: absolute;
                background: white;
                border-radius: 5px;
                padding: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                z-index: 1000;
                display: none;
            }
        </style>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Map Container --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Live Rider Map</h3>
                    <div id="adminMap"></div>
                    <div id="riderPopup" class="rider-popup"></div>
                </div>
            </div>

            {{-- Other dashboard widgets can go here --}}
             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 text-gray-900">
                     {{ __("Other Stats Placeholder") }}
                 </div>
             </div>

        </div>
    </div>

    {{-- Include OpenStreetMap and OSRM scripts --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize map once DOM is loaded
                initializeMap();
                
                function initializeMap() {
                    const mapContainer = document.getElementById('adminMap');
                    const riderPopup = document.getElementById('riderPopup');
                    
                    if (!mapContainer) {
                        console.error('Map container not found!');
                        return;
                    }
                    
                    // Initial coordinates (Tagum City)
                    const initialLat = 7.4478;
                    const initialLng = 125.8099;
                    const initialZoom = 13;
                    
                    // Create an iframe for OpenStreetMap with specific coordinates and zoom
                    const iframe = document.createElement('iframe');
                    iframe.width = '100%';
                    iframe.height = '600';
                    iframe.frameBorder = '0';
                    iframe.scrolling = 'no';
                    iframe.marginHeight = '0';
                    iframe.marginWidth = '0';
                    iframe.src = `https://www.openstreetmap.org/export/embed.html?bbox=${initialLng-0.1},${initialLat-0.1},${initialLng+0.1},${initialLat+0.1}&layer=mapnik&marker=${initialLat},${initialLng}`;
                    
                    // Clear and append the iframe to the map container
                    mapContainer.innerHTML = '';
                    mapContainer.appendChild(iframe);
                    
                    // Store rider markers
                    let riderMarkers = {};
                    
                    // Function to update rider positions
                    function updateRiderMarkers(riders) {
                        // Remove current markers
                        Array.from(mapContainer.querySelectorAll('.rider-marker')).forEach(marker => {
                            marker.remove();
                        });
                        
                        // Add new markers
                        riders.forEach(rider => {
                            // Create marker element
                            const marker = document.createElement('div');
                            marker.className = 'rider-marker';
                            marker.innerHTML = `
                                <span class="status-indicator status-${rider.status}"></span>
                                <span class="rider-name">${rider.name.split(' ')[0]}</span>
                            `;
                            
                            // Position the marker - this would require mapping real coordinates to iframe position
                            // For demonstration, we'll use placeholder positioning for now
                            marker.style.left = `${Math.random() * 80 + 10}%`;  // Random position for demo
                            marker.style.top = `${Math.random() * 80 + 10}%`;   // Random position for demo
                            
                            // Store marker reference
                            riderMarkers[rider.id] = marker;
                            
                            // Add click event for popup
                            marker.addEventListener('click', function(e) {
                                e.stopPropagation();
                                
                                // Show popup with rider details
                                riderPopup.innerHTML = `
                                    <b>${rider.name}</b><br>
                                    Status: ${rider.status}<br>
                                    Last Update: ${rider.location_updated_at || 'N/A'}
                                `;
                                riderPopup.style.display = 'block';
                                riderPopup.style.left = (e.pageX + 10) + 'px';
                                riderPopup.style.top = (e.pageY + 10) + 'px';
                            });
                            
                            // Add marker to map
                            mapContainer.appendChild(marker);
                        });
                    }
                    
                    // Close popup when clicking outside
                    document.addEventListener('click', function() {
                        riderPopup.style.display = 'none';
                    });

                    // Function to fetch rider data from API
                    async function fetchRiderData() {
                        const apiUrl = '/api/admin/active-riders';

                        try {
                            const response = await fetch(apiUrl, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                                }
                            });
                            
                            if (!response.ok) {
                                console.error('Failed to fetch rider data:', response.status, response.statusText);
                                return;
                            }
                            
                            const data = await response.json();
                            if (data.riders) {
                                updateRiderMarkers(data.riders);
                            }
                        } catch (error) {
                            console.error('Error fetching rider data:', error);
                        }
                    }

                    // Initial rider data fetch
                    fetchRiderData();
                    
                    // Periodic rider data updates
                    setInterval(fetchRiderData, 15000);
                }
                
                // Function to calculate route using OSRM API
                async function calculateRoute(startLat, startLng, endLat, endLng) {
                    try {
                        // OSRM API endpoint for routing
                        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;
                        
                        const response = await fetch(osrmUrl);
                        if (!response.ok) {
                            throw new Error('Route calculation failed');
                        }
                        
                        const data = await response.json();
                        if (data.routes && data.routes.length > 0) {
                            return data.routes[0];  // Return the first route
                        } else {
                            throw new Error('No routes found');
                        }
                    } catch (error) {
                        console.error('Error calculating route:', error);
                        return null;
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>