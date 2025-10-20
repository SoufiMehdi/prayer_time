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
        <div className={`bg-gradient-to-br ${colorClass} rounded-3xl shadow-2xl p-6 border border-white/20`}>
            <h2 className="text-xl font-bold text-white/90 mb-4 flex items-center gap-2">
                <Clock className="w-6 h-6" />
                Prochaine prière
            </h2>

            {/* Section prière */}
            <div className="flex items-center gap-4 mb-6">
                <Icon className="w-16 h-16 text-white drop-shadow-lg flex-shrink-0" />
                <div>
                    <h3 className="text-4xl font-bold text-white mb-1">{nextPrayer.name}</h3>
                    <p className="text-2xl font-semibold text-white/95">{nextPrayer.time}</p>
                </div>
            </div>

            {/* Compte à rebours */}
            {timeRemaining && (
                <div className="bg-white/20 backdrop-blur-lg rounded-2xl p-6 border border-white/30">
                    <p className="text-white/90 text-center mb-3">Temps restant</p>
                    <div className="flex justify-center gap-2">
                        <div className="text-center">
                            <div className="text-3xl font-bold text-white">{formatTimeUnit(timeRemaining.hours)}</div>
                            <div className="text-xs text-white/80 mt-1">heures</div>
                        </div>
                        <div className="text-3xl font-bold text-white self-start">:</div>
                        <div className="text-center">
                            <div className="text-3xl font-bold text-white">{formatTimeUnit(timeRemaining.minutes)}</div>
                            <div className="text-xs text-white/80 mt-1">minutes</div>
                        </div>
                        <div className="text-3xl font-bold text-white self-start">:</div>
                        <div className="text-center">
                            <div className="text-3xl font-bold text-white">{formatTimeUnit(timeRemaining.seconds)}</div>
                            <div className="text-xs text-white/80 mt-1">secondes</div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default NextPrayerCard;
