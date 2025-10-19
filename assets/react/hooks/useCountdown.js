import { useState, useEffect, useRef } from 'react';
import { calculateTimeRemaining } from '../utils/timeHelpers';

export const useCountdown = (nextPrayer, onComplete) => {
    const [timeRemaining, setTimeRemaining] = useState(null);
    const hasCompletedRef = useRef(false);

    useEffect(() => {
        if (!nextPrayer?.time) return;

        // Réinitialiser le flag quand la prière change
        hasCompletedRef.current = false;

        const updateTimer = () => {
            const remaining = calculateTimeRemaining(nextPrayer.time);
            setTimeRemaining(remaining);

            // Si le compte à rebours atteint zéro et qu'on n'a pas encore appelé onComplete
            if (remaining.total <= 0 && !hasCompletedRef.current && onComplete) {
                hasCompletedRef.current = true; // Éviter les appels multiples
                onComplete();
            }
        };

        updateTimer();
        const interval = setInterval(updateTimer, 1000);

        return () => clearInterval(interval);
    }, [nextPrayer?.time, nextPrayer?.name]); // Ne pas mettre onComplete dans les dépendances

    return timeRemaining;
};
