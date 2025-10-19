import React from 'react';
import { Clock, Moon, Sun, Sunrise, Sunset } from 'lucide-react';
import { getPrayerColor } from '../utils/prayerHelpers';
import { formatTimeUnit } from '../utils/timeHelpers';

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

const NextPrayerCard = ({ nextPrayer, timeRemaining }) => {
    if (!nextPrayer) return null;

    const Icon = getPrayerIcon(nextPrayer.name);
    const colorClass = getPrayerColor(nextPrayer.name);

    return (
        <div className={`bg-gradient-to-br ${colorClass} rounded-3xl shadow-2xl p-10 border border-white/20`}>
            <h2 className="text-2xl font-bold text-white/90 mb-6 flex items-center gap-3">
                <Clock className="w-7 h-7" />
                L'heure de la prochaine prière avec le compte à rebours
            </h2>
            <div className="flex items-center justify-between flex-wrap gap-8">
                <div className="flex items-center gap-6">
                    <Icon className="w-24 h-24 text-white drop-shadow-lg" />
                    <div>
                        <p className="text-white/90 text-xl mb-2">Prochaine prière</p>
                        <h3 className="text-6xl font-bold text-white mb-3">{nextPrayer.name}</h3>
                        <p className="text-3xl font-semibold text-white/95">{nextPrayer.time}</p>
                    </div>
                </div>
                {timeRemaining && (
                    <div className="bg-white/20 backdrop-blur-lg rounded-2xl p-8 text-center border border-white/30">
                        <p className="text-white/90 text-lg mb-3">Temps restant</p>
                        <div className="flex gap-4">
                            <div className="text-center">
                                <div className="text-5xl font-bold text-white">
                                    {formatTimeUnit(timeRemaining.hours)}
                                </div>
                                <div className="text-sm text-white/80 mt-1">heures</div>
                            </div>
                            <div className="text-5xl font-bold text-white">:</div>
                            <div className="text-center">
                                <div className="text-5xl font-bold text-white">
                                    {formatTimeUnit(timeRemaining.minutes)}
                                </div>
                                <div className="text-sm text-white/80 mt-1">minutes</div>
                            </div>
                            <div className="text-5xl font-bold text-white">:</div>
                            <div className="text-center">
                                <div className="text-5xl font-bold text-white">
                                    {formatTimeUnit(timeRemaining.seconds)}
                                </div>
                                <div className="text-sm text-white/80 mt-1">secondes</div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default NextPrayerCard;
