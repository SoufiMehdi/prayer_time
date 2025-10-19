export const calculateTimeRemaining = (prayerTime) => {
    const now = new Date();
    const [hours, minutes] = prayerTime.split(':');
    const prayer = new Date();
    prayer.setHours(parseInt(hours), parseInt(minutes), 0, 0);

    let diff = prayer - now;

    // Si la différence est négative de plus de 2 minutes, c'est pour demain
    // Sinon on garde le négatif pour permettre le rafraîchissement
    if (diff < -120000) { // -2 minutes en millisecondes
        prayer.setDate(prayer.getDate() + 1);
        diff = prayer - now;
    }

    const hoursLeft = Math.max(0, Math.floor(diff / (1000 * 60 * 60)));
    const minutesLeft = Math.max(0, Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)));
    const secondsLeft = Math.max(0, Math.floor((diff % (1000 * 60)) / 1000));

    return {
        hours: hoursLeft,
        minutes: minutesLeft,
        seconds: secondsLeft,
        total: diff
    };
};

export const formatTimeUnit = (value) => {
    return String(value).padStart(2, '0');
};
