import { useState, useEffect, useCallback } from 'react';
import { API_ENDPOINT } from '../constants/prayerConfig';

export const usePrayerData = (initialCity, initialCountry) => {
    const [prayerData, setPrayerData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [city, setCity] = useState(initialCity);
    const [country, setCountry] = useState(initialCountry);

    const fetchPrayerTimes = useCallback(async (searchCity, searchCountry) => {
        const cityToUse = searchCity || city;
        const countryToUse = searchCountry || country;

        setLoading(true);
        setError(null);

        try {
            const url = `${API_ENDPOINT}?city=${encodeURIComponent(cityToUse)}&country=${encodeURIComponent(countryToUse)}`;
            console.log('🔄 Fetching prayer times:', url);

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des données');
            }

            const data = await response.json();
            console.log('✅ Prayer data received:', data);
            setPrayerData(data);
        } catch (err) {
            console.error('❌ Error fetching prayer times:', err);
            setError(err.message);
        } finally {
            setLoading(false);
        }
    }, [city, country]);

    const refetch = useCallback(() => {
        console.log('🔄 Refetch triggered');
        return fetchPrayerTimes(city, country);
    }, [fetchPrayerTimes, city, country]);

    useEffect(() => {
        fetchPrayerTimes(city, country);
    }, []); // Seulement au montage

    return {
        prayerData,
        loading,
        error,
        city,
        country,
        setCity,
        setCountry,
        fetchPrayerTimes,
        refetch,
    };
};
