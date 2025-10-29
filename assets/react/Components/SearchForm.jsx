import React from 'react';
import DateDisplay  from "./DateDisplay";
import { MapPin } from 'lucide-react';

const SearchForm = ({ city, country, onCityChange, onCountryChange, onSubmit, date, hijriData, location }) => {
    return (
        <div className="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-8 border border-white/20">
            <h2 className="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <MapPin className="w-7 h-7" />
                Les champs de sélection
            </h2>
            <form onSubmit={onSubmit} className="flex gap-4 flex-wrap">
                <input
                    type="text"
                    value={city}
                    onChange={(e) => onCityChange(e.target.value)}
                    placeholder="Ville"
                    className="flex-1 min-w-[200px] px-6 py-4 bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl text-white placeholder-white/60 focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none text-lg"
                />
                <input
                    type="text"
                    value={country}
                    onChange={(e) => onCountryChange(e.target.value)}
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
            <DateDisplay
                gregorianDate={date}
                hijriData={hijriData}
                location={location}
            />
        </div>
    );
};

export default SearchForm;
