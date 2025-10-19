import React from 'react';
import { createRoot } from 'react-dom/client';
import PrayerApp from './react/Components/PrayerApp';
import './styles/app.css';

const container = document.getElementById('prayer-app');
if (container) {
    const root = createRoot(container);
    root.render(<PrayerApp />);
}
