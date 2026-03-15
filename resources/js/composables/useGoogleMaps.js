/**
 * Load Google Maps JavaScript API with Places library.
 * Requires VITE_GOOGLE_MAPS_API_KEY in .env.
 * @returns {Promise<typeof google>}
 */
export function useGoogleMaps() {
    const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

    if (!apiKey) {
        return Promise.reject(new Error('VITE_GOOGLE_MAPS_API_KEY is not set'));
    }

    if (typeof window !== 'undefined' && window.google?.maps) {
        return Promise.resolve(window.google);
    }

    return new Promise((resolve, reject) => {
        const callbackName = '__googleMapsLoaded_' + Date.now();
        window[callbackName] = () => {
            delete window[callbackName];
            if (window.google?.maps) {
                resolve(window.google);
            } else {
                reject(new Error('Google Maps failed to load'));
            }
        };
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=${callbackName}`;
        script.async = true;
        script.defer = true;
        script.onerror = () => {
            delete window[callbackName];
            reject(new Error('Failed to load Google Maps script'));
        };
        document.head.appendChild(script);
    });
}
