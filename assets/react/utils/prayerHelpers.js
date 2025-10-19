import { PRAYER_COLORS, DEFAULT_COLOR } from '../constants/prayerConfig';

export const getPrayerColor = (name) => {
    return PRAYER_COLORS[name] || DEFAULT_COLOR;
};

export const filterPrayerTimings = (timings) => {
    if (!timings) return {};

    const { Sunrise, ...prayers } = timings;
    return prayers;
};

export const getSunriseTime = (timings) => {
    return timings?.Sunrise || null;
};


