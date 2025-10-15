import React, { useState, useEffect } from 'react';
import { Clock, MapPin, Calendar, Loader2, Sun, Moon, Sunrise, Sunset } from 'lucide-react';

const PrayerApp = () => {
    const [prayerData, setPrayerData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [city, setCity] = useState('Clermont-Ferrand');
    const [country, setCountry] = useState('France');
    const [timeRemaining, setTimeRemaining] = useState(null);

    const fetchPrayerTimes = async (searchCity = city, searchCountry = country) => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch(`/api/prayer/time?city=${searchCity}&country=${searchCountry}`);

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
    };

    // Calculer le temps restant en temps réel
    useEffect(() => {
        if (!prayerData?.nextPrayer) return;

        const calculateTimeRemaining = () => {
            const now = new Date();
            const [hours, minutes] = prayerData.nextPrayer.time.split(':');
            const prayerTime = new Date();
            prayerTime.setHours(parseInt(hours), parseInt(minutes), 0, 0);

            let diff = prayerTime - now;

            // Si la différence est négative, la prière est pour demain
            if (diff < 0) {
                prayerTime.setDate(prayerTime.getDate() + 1);
                diff = prayerTime - now;
            }

            const hoursLeft = Math.floor(diff / (1000 * 60 * 60));
            const minutesLeft = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const secondsLeft = Math.floor((diff % (1000 * 60)) / 1000);

            return { hours: hoursLeft, minutes: minutesLeft, seconds: secondsLeft, total: diff };
        };

        const updateTimer = () => {
            const remaining = calculateTimeRemaining();
            setTimeRemaining(remaining);

            // Si le compte à rebours atteint zéro, actualiser les données
            if (remaining.total <= 0) {
                fetchPrayerTimes();
            }
        };

        updateTimer();
        const interval = setInterval(updateTimer, 1000);

        return () => clearInterval(interval);
    }, [prayerData?.nextPrayer]);

    useEffect(() => {
        fetchPrayerTimes();
    }, []);

    const handleSearch = (e) => {
        e.preventDefault();
        fetchPrayerTimes(city, country);
    };

    const getPrayerIcon = (name) => {
        const icons = {
            'Fajr': Moon,
            'Sunrise': Sunrise,
            'Dhuhr': Sun,
            'Asr': Sun,
            'Maghrib': Sunset,
            'Isha': Moon,
        };
        return icons[name] || Clock;
    };

    const getPrayerColor = (name) => {
        const colors = {
            'Fajr': 'from-indigo-500 to-purple-600',
            'Sunrise': 'from-orange-400 to-pink-500',
            'Dhuhr': 'from-yellow-400 to-orange-500',
            'Asr': 'from-amber-400 to-orange-600',
            'Maghrib': 'from-orange-500 to-red-600',
            'Isha': 'from-indigo-600 to-purple-700',
        };
        return colors[name] || 'from-gray-400 to-gray-600';
    };

    if (loading && !prayerData) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
                <div className="text-center">
                    <Loader2 className="w-16 h-16 animate-spin text-indigo-600 mx-auto mb-4" />
                    <p className="text-gray-600 text-lg">Chargement des horaires...</p>
                </div>
            </div>
        );
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
                            onClick={() => fetchPrayerTimes()}
                            className="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Réessayer
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 p-6">
            <div className="max-w-7xl mx-auto space-y-6">

                {/* Bloc 1: Champs de sélection */}
                <div className="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-8 border border-white/20">
                    <h2 className="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                        <MapPin className="w-7 h-7" />
                        Les champs de sélection
                    </h2>
                    <form onSubmit={handleSearch} className="flex gap-4 flex-wrap">
                        <input
                            type="text"
                            value={city}
                            onChange={(e) => setCity(e.target.value)}
                            placeholder="Ville"
                            className="flex-1 min-w-[200px] px-6 py-4 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none text-lg"
                        />
                        <input
                            type="text"
                            value={country}
                            onChange={(e) => setCountry(e.target.value)}
                            placeholder="Pays"
                            className="flex-1 min-w-[200px] px-6 py-4 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none text-lg"
                        />
                        <button
                            type="submit"
                            className="px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-pink-600 transition-all shadow-lg hover:shadow-xl"
                        >
                            Rechercher
                        </button>
                    </form>
                    <div className="mt-4 flex items-center gap-3 text-white/80">
                        <Calendar className="w-5 h-5" />
                        <span className="text-lg">{prayerData?.timing?.date}</span>
                        <span className="mx-2">•</span>
                        <span className="text-lg">{prayerData?.city}, {prayerData?.country}</span>
                    </div>
                </div>

                {/* Bloc 2: Prochaine prière avec compte à rebours */}
                {prayerData?.nextPrayer && (
                    <div className={`bg-gradient-to-br ${getPrayerColor(prayerData.nextPrayer.name)} rounded-3xl shadow-2xl p-10 border border-white/20`}>
                        <h2 className="text-2xl font-bold text-white/90 mb-6 flex items-center gap-3">
                            <Clock className="w-7 h-7" />
                            L'heure de la prochaine prière avec le compte à rebours
                        </h2>
                        <div className="flex items-center justify-between flex-wrap gap-8">
                            <div className="flex items-center gap-6">
                                {React.createElement(getPrayerIcon(prayerData.nextPrayer.name), {
                                    className: "w-24 h-24 text-white drop-shadow-lg"
                                })}
                                <div>
                                    <p className="text-white/90 text-xl mb-2">Prochaine prière</p>
                                    <h3 className="text-6xl font-bold text-white mb-3">{prayerData.nextPrayer.name}</h3>
                                    <p className="text-3xl font-semibold text-white/95">{prayerData.nextPrayer.time}</p>
                                </div>
                            </div>
                            <div className="bg-white/20 backdrop-blur-lg rounded-2xl p-8 text-center border border-white/30">
                                <p className="text-white/90 text-lg mb-3">Temps restant</p>
                                {timeRemaining && (
                                    <div className="flex gap-4">
                                        <div className="text-center">
                                            <div className="text-5xl font-bold text-white">{String(timeRemaining.hours).padStart(2, '0')}</div>
                                            <div className="text-sm text-white/80 mt-1">heures</div>
                                        </div>
                                        <div className="text-5xl font-bold text-white">:</div>
                                        <div className="text-center">
                                            <div className="text-5xl font-bold text-white">{String(timeRemaining.minutes).padStart(2, '0')}</div>
                                            <div className="text-sm text-white/80 mt-1">minutes</div>
                                        </div>
                                        <div className="text-5xl font-bold text-white">:</div>
                                        <div className="text-center">
                                            <div className="text-5xl font-bold text-white">{String(timeRemaining.seconds).padStart(2, '0')}</div>
                                            <div className="text-sm text-white/80 mt-1">secondes</div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                {/* Bloc 3: Grille des prières */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {/* Sous-bloc gauche: Liste des prières */}
                    <div className="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-8 border border-white/20">
                        <h2 className="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                            <Moon className="w-7 h-7" />
                            La liste des prières
                        </h2>
                        <div className="space-y-4">
                            {Object.entries(prayerData?.timing?.timings || {}).map(([name, time]) => {
                                if (name === 'Sunrise') return null;

                                const Icon = getPrayerIcon(name);
                                const isNext = prayerData?.nextPrayer?.name === name;

                                return (
                                    <div
                                        key={name}
                                        className={`bg-white/10 backdrop-blur-sm rounded-xl p-5 border transition-all ${
                                            isNext
                                                ? 'border-purple-400 ring-2 ring-purple-400 shadow-lg'
                                                : 'border-white/20 hover:border-white/40'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-4">
                                                <div className={`bg-gradient-to-br ${getPrayerColor(name)} p-3 rounded-lg`}>
                                                    <Icon className="w-6 h-6 text-white" />
                                                </div>
                                                <h3 className="text-xl font-bold text-white">{name}</h3>
                                            </div>
                                            <div className="flex items-center gap-3">
                                                <p className="text-3xl font-bold text-white">{time}</p>
                                                {isNext && (
                                                    <span className="bg-purple-500 text-white text-xs font-semibold px-3 py-1 rounded-full">
                            En cours
                          </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Sous-bloc droite: Citations et rappels */}
                    <div className="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-8 border border-white/20">
                        <h2 className="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                            <Sun className="w-7 h-7" />
                            Citation et rappels
                        </h2>

                        {/* Lever du soleil */}
                        {prayerData?.timing?.timings?.Sunrise && (
                            <div className="bg-gradient-to-r from-orange-500/20 to-yellow-500/20 backdrop-blur-sm rounded-xl p-6 mb-6 border border-orange-300/30">
                                <div className="flex items-center gap-4">
                                    <Sunrise className="w-10 h-10 text-orange-300" />
                                    <div>
                                        <h3 className="text-lg font-semibold text-white">Lever du soleil</h3>
                                        <p className="text-3xl font-bold text-orange-200">
                                            {prayerData.timing.timings.Sunrise}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Citation spirituelle */}
                        <div className="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/20">
                            <p className="text-white/90 text-lg italic leading-relaxed mb-4">
                                "La prière est le pilier de la religion"
                            </p>
                            <p className="text-white/70 text-sm">
                                Méthode de calcul : {prayerData?.timing?.meta?.method?.name}
                            </p>
                            <div className="mt-4 pt-4 border-t border-white/20">
                                <p className="text-white/60 text-sm">
                                    Coordonnées : {prayerData?.timing?.meta?.latitude?.toFixed(4)}°, {prayerData?.timing?.meta?.longitude?.toFixed(4)}°
                                </p>
                                <p className="text-white/60 text-sm mt-1">
                                    Fuseau horaire : {prayerData?.timing?.meta?.timezone}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default PrayerApp;
