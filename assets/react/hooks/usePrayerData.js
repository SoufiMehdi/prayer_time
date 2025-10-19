import { useState, useEffect, useCallback } from 'react';
import { API_ENDPOINT } from '../constants/prayerConfig';

export const usePrayerData = (initialCity, initialCountry) => {
    const [prayerData, setPrayerData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [city, setCity] = useState(initialCity);
    const [country, setCountry] = useState(initialCountry);

    const fetchPrayerTimes = useCallback(async (searchCity = city, searchCountry = country) => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch(
                `${API_ENDPOINT}?city=${encodeURIComponent(searchCity)}&country=${encodeURIComponent(searchCountry)}`
            );

            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des données');
            }

            const data = await response.json();
            setPrayerData(data);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    }, []); // Pas de dépendances pour éviter les re-créations

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
        refetch: () => fetchPrayerTimes(city, country),
    };
};
