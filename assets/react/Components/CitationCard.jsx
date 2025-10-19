import React from 'react';
import { Sun, Sunrise } from 'lucide-react';

const CitationCard = ({ sunriseTime, methodName, latitude, longitude, timezone }) => {
    return (
        <div className="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-8 border border-white/20">
            <h2 className="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <Sun className="w-7 h-7" />
                Citation et rappels
            </h2>

            {/* Lever du soleil */}
            {sunriseTime && (
                <div className="bg-gradient-to-r from-orange-500/20 to-yellow-500/20 backdrop-blur-sm rounded-xl p-6 mb-6 border border-orange-300/30">
                    <div className="flex items-center gap-4">
                        <Sunrise className="w-10 h-10 text-orange-300" />
                        <div>
                            <h3 className="text-lg font-semibold text-white">Lever du soleil</h3>
                            <p className="text-3xl font-bold text-orange-200">{sunriseTime}</p>
                        </div>
                    </div>
                </div>
            )}

            {/* Citation spirituelle */}
            <div className="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/20">
                <p className="text-white/90 text-lg italic leading-relaxed mb-4">
                    "La prière est le pilier de la religion"
                </p>
                {methodName && (
                    <p className="text-white/70 text-sm">
                        Méthode de calcul : {methodName}
                    </p>
                )}
                <div className="mt-4 pt-4 border-t border-white/20">
                    {latitude && longitude && (
                        <p className="text-white/60 text-sm">
                            Coordonnées : {latitude.toFixed(4)}°, {longitude.toFixed(4)}°
                        </p>
                    )}
                    {timezone && (
                        <p className="text-white/60 text-sm mt-1">
                            Fuseau horaire : {timezone}
                        </p>
                    )}
                </div>
            </div>
        </div>
    );
};

export default CitationCard;
