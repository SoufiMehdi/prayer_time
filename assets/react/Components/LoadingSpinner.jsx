import React from 'react';
import { Loader2 } from 'lucide-react';

const LoadingSpinner = ({ message = "Chargement des horaires..." }) => {
    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
            <div className="text-center">
                <Loader2 className="w-16 h-16 animate-spin text-indigo-600 mx-auto mb-4" />
                <p className="text-gray-600 text-lg">{message}</p>
            </div>
        </div>
    );
};

export default LoadingSpinner;
