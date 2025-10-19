import React, { useCallback } from 'react';
import LoadingSpinner from './LoadingSpinner';
import SearchForm from './SearchForm';
import NextPrayerCard from './NextPrayerCard';
import PrayerList from './PrayerList';
import CitationCard from './CitationCard';
import { usePrayerData } from '../hooks/usePrayerData';
import { useCountdown } from '../hooks/useCountdown';
import { filterPrayerTimings, getSunriseTime } from '../utils/prayerHelpers';
import { DEFAULT_CITY, DEFAULT_COUNTRY } from '../constants/prayerConfig';

const PrayerApp = () => {
    const {
        prayerData,
        loading,
        error,
        city,
        country,
        setCity,
        setCountry,
        fetchPrayerTimes,
        refetch,
    } = usePrayerData(DEFAULT_CITY, DEFAULT_COUNTRY);

    // Mémoriser la fonction de refetch pour éviter les re-renders
    const handleCountdownComplete = useCallback(() => {
        console.log('⏰ Countdown complete, refetching data...');
        refetch();
    }, [refetch]);

    const timeRemaining = useCountdown(prayerData?.nextPrayer, handleCountdownComplete);

    const handleSearch = (e) => {
        e.preventDefault();
        fetchPrayerTimes(city, country);
    };

    if (loading && !prayerData) {
        return <LoadingSpinner />;
    }

    if (error) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
                <div className="bg-white rounded-2xl shadow-2xl p-8 max-w-md">
                    <div className="text-center">
                        <div className="text-red-500 text-5xl mb-4">⚠️</div>
                        <h2 className="text-2xl font-bold text-gray-800 mb-2">Erreur</h2>
                        <p className="text-gray-600 mb-6">{error}</p>
                        <button
                            onClick={refetch}
                            className="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Réessayer
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    const prayerTimings = filterPrayerTimings(prayerData?.timing?.timings);
    const sunriseTime = getSunriseTime(prayerData?.timing?.timings);

    return (
        <div className="min-h-screen bg-gradient-to-br from-islamic-green-900 via-islamic-green-800 to-islamic-green-950 p-6">
            <div className="max-w-7xl mx-auto space-y-6">

                {/* Bloc 1: Champs de sélection */}
                <SearchForm
                    city={city}
                    country={country}
                    onCityChange={setCity}
                    onCountryChange={setCountry}
                    onSubmit={handleSearch}
                    date={prayerData?.timing?.date}
                    location={`${prayerData?.city}, ${prayerData?.country}`}
                />

                {/* Bloc 2: Prochaine prière avec compte à rebours */}
                <NextPrayerCard
                    nextPrayer={prayerData?.nextPrayer}
                    timeRemaining={timeRemaining}
                />

                {/* Bloc 3: Grille des prières */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Sous-bloc gauche: Liste des prières */}
                    <PrayerList
                        timings={prayerTimings}
                        nextPrayerName={prayerData?.nextPrayer?.name}
                    />

                    {/* Sous-bloc droite: Citations et rappels */}
                    <CitationCard
                        sunriseTime={sunriseTime}
                        methodName={prayerData?.timing?.meta?.method?.name}
                        latitude={prayerData?.timing?.meta?.latitude}
                        longitude={prayerData?.timing?.meta?.longitude}
                        timezone={prayerData?.timing?.meta?.timezone}
                    />
                </div>
            </div>
        </div>
    );
};

export default PrayerApp;
