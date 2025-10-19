import { useState, useEffect, useRef } from 'react';
import { calculateTimeRemaining } from '../utils/timeHelpers';

export const useCountdown = (nextPrayer, onComplete) => {
    const [timeRemaining, setTimeRemaining] = useState(null);
    const hasCompletedRef = useRef(false);
    const lastPrayerTimeRef = useRef(null);

    useEffect(() => {
        if (!nextPrayer?.time) return;

        // Si la prière a changé, réinitialiser le flag
        if (lastPrayerTimeRef.current !== nextPrayer.time) {
            hasCompletedRef.current = false;
            lastPrayerTimeRef.current = nextPrayer.time;
        }

        const updateTimer = () => {
            const remaining = calculateTimeRemaining(nextPrayer.time);
            setTimeRemaining(remaining);

            // Si le compte arrive à 0 ou devient négatif
            if (remaining.total <= 1000 && !hasCompletedRef.current && onComplete) {
                hasCompletedRef.current = true;
                console.log('⏰ Compte à rebours terminé, rafraîchissement des données...');
                setTimeout(() => {
                    onComplete();
                }, 1000); // Attendre 1 seconde avant de rafraîchir
            }
        };

        updateTimer();
        const interval = setInterval(updateTimer, 1000);

        return () => clearInterval(interval);
    }, [nextPrayer?.time, nextPrayer?.name, onComplete]);

    return timeRemaining;
};
