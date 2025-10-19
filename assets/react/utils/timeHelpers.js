export const calculateTimeRemaining = (prayerTime) => {
    const now = new Date();
    const [hours, minutes] = prayerTime.split(':');
    const prayer = new Date();
    prayer.setHours(parseInt(hours), parseInt(minutes), 0, 0);

    let diff = prayer - now;

    // Si la différence est négative, la prière est pour demain
    if (diff < 0) {
        prayer.setDate(prayer.getDate() + 1);
        diff = prayer - now;
    }

    const hoursLeft = Math.floor(diff / (1000 * 60 * 60));
    const minutesLeft = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const secondsLeft = Math.floor((diff % (1000 * 60)) / 1000);

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
