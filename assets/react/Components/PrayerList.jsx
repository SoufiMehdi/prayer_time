import React from 'react';
import { Moon, Sun, Sunrise, Sunset, Clock } from 'lucide-react';
import { getPrayerColor } from '../utils/prayerHelpers';

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

const PrayerList = ({ timings, nextPrayerName }) => {
    if (!timings) return null;

    return (
        <div className="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-8 border border-white/20">
            <h2 className="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <Moon className="w-7 h-7" />
                La liste des prières
            </h2>
            <div className="space-y-4">
                {Object.entries(timings).map(([name, time]) => {
                    const Icon = getPrayerIcon(name);
                    const colorClass = getPrayerColor(name);
                    const isNext = nextPrayerName === name;

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
                                    <div className={`bg-gradient-to-br ${colorClass} p-3 rounded-lg`}>
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
    );
};

export default PrayerList;
