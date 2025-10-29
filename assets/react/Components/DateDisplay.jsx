import React from 'react';
import { Calendar, Moon, AlertCircle } from 'lucide-react';

const DateDisplay = ({ gregorianDate, hijriData, location }) => {
    if (!gregorianDate && !hijriData) return null;

    const hasHolidays = hijriData?.holidays && hijriData.holidays.length > 0;

    return (
        <div className="mt-6 space-y-4">
            {/* Dates */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {/* Date Grégorienne */}
                {gregorianDate && (
                    <div className="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                        <div className="flex items-center gap-3">
                            <Calendar className="w-5 h-5 text-white/80" />
                            <div>
                                <p className="text-white/60 text-sm">Date Grégorienne</p>
                                <p className="text-white text-lg font-semibold">{gregorianDate}</p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Date Hijri */}
                {hijriData && (
                    <div className="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 backdrop-blur-sm rounded-2xl p-4 border border-emerald-400/30">
                        <div className="flex items-center gap-3">
                            <Moon className="w-5 h-5 text-emerald-300" />
                            <div className="flex-1">
                                <p className="text-white/60 text-sm">Date Hijri</p>
                                <div className="flex items-baseline gap-2">
                  <span className="text-white text-lg font-semibold">
                    {hijriData.day}
                  </span>
                                    <span className="text-emerald-200 text-xl font-bold">
                    {hijriData.month?.en || hijriData.month?.ar}
                  </span>
                                    <span className="text-white text-lg font-semibold">
                    {hijriData.year}
                  </span>
                                </div>
                                {hijriData.month?.ar && (
                                    <p className="text-white/50 text-sm mt-1 font-arabic">
                                        {hijriData.month.ar}
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* Localisation */}
            {location && (
                <div className="flex items-center gap-2 text-white/70">
                    <span className="text-lg">📍 {location}</span>
                </div>
            )}

            {/* Holidays */}
            {hasHolidays && (
                <div className="bg-gradient-to-r from-amber-500/20 to-orange-500/20 backdrop-blur-sm rounded-2xl p-4 border border-amber-400/40">
                    <div className="flex items-start gap-3">
                        <AlertCircle className="w-5 h-5 text-amber-300 flex-shrink-0 mt-0.5" />
                        <div className="flex-1">
                            <p className="text-amber-200 font-semibold mb-2">🎉 Jours spéciaux</p>
                            <div className="space-y-1">
                                {hijriData.holidays.map((holiday, index) => (
                                    <p key={index} className="text-white/90">
                                        • {holiday}
                                    </p>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Adjusted Holidays (si présent) */}
            {hijriData?.adjustedHolidays && hijriData.adjustedHolidays.length > 0 && (
                <div className="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm rounded-2xl p-4 border border-purple-400/40">
                    <div className="flex items-start gap-3">
                        <AlertCircle className="w-5 h-5 text-purple-300 flex-shrink-0 mt-0.5" />
                        <div className="flex-1">
                            <p className="text-purple-200 font-semibold mb-2">📅 Événements ajustés</p>
                            <div className="space-y-1">
                                {hijriData.adjustedHolidays.map((holiday, index) => (
                                    <p key={index} className="text-white/90">
                                        • {holiday}
                                    </p>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DateDisplay;
