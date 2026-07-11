// api-service.js

/**
 * Provides stock data fetching using only Alpha Vantage free endpoints.
 * Handles rate limiting and returns parsed time series or company profile data.
 */

// Your Alpha Vantage API key (free-tier)
const ALPHA_VANTAGE_API_KEY = '09SP6T4P7W333QWJ';

// Rate limit tracking
let alphaVantageCallCount = 0;
let alphaVantageRateLimited = false;
let lastResetTime = Date.now();
let alphaVantageResetTimer = null;

/**
 * Returns true if we can still make Alpha Vantage calls under the free-tier limits.
 */
function isAlphaVantageAvailable() {
    const now = Date.now();
    // Reset the count once per minute
    if (now - lastResetTime >= 60_000) {
        alphaVantageCallCount = 0;
        lastResetTime = now;
        clearTimeout(alphaVantageResetTimer);
        alphaVantageRateLimited = false;
    }
    return !alphaVantageRateLimited && alphaVantageCallCount < 5;
}

/**
 * Marks Alpha Vantage as rate-limited for one minute.
 */
function setAlphaVantageRateLimited() {
    alphaVantageRateLimited = true;
    alphaVantageResetTimer = setTimeout(() => {
        alphaVantageRateLimited = false;
        alphaVantageCallCount = 0;
        lastResetTime = Date.now();
    }, 60_000);
}

/**
 * Fetches stock time series free-tier data from Alpha Vantage.
 *
 * @param {string} symbol    // e.g. 'AAPL'
 * @param {string} timeframe // 'daily', 'intraday', 'weekly', 'monthly'
 * @returns {Promise<Object>} // { source, symbol, dates, prices, opens, highs, lows, volumes }
 */
export async function fetchStockTimeSeries(symbol, timeframe = 'daily') {
    if (!isAlphaVantageAvailable()) {
        throw new Error('Alpha Vantage rate limit exceeded. Please wait a moment and try again.');
    }

    try {
        return await fetchFromAlphaVantage(symbol, timeframe);
    } catch (err) {
        // If error message indicates rate limit, set limited state
        if (/rate limit/i.test(err.message)) {
            setAlphaVantageRateLimited();
            throw new Error('Alpha Vantage rate limit reached. Please wait a moment and try again.');
        }
        throw err;
    }
}

async function fetchFromAlphaVantage(symbol, timeframe) {
    alphaVantageCallCount++;

    let functionName;
    let intervalParam = '';

    switch (timeframe) {
        case 'intraday':
            functionName = 'TIME_SERIES_INTRADAY';
            intervalParam = '&interval=5min';
            break;
        case 'weekly':
            functionName = 'TIME_SERIES_WEEKLY';
            break;
        case 'monthly':
            functionName = 'TIME_SERIES_MONTHLY';
            break;
        case 'daily':
        default:
            functionName = 'TIME_SERIES_DAILY_ADJUSTED';
            break;
    }

    const url = `https://www.alphavantage.co/query?function=${functionName}&symbol=${symbol}${intervalParam}&outputsize=compact&apikey=${ALPHA_VANTAGE_API_KEY}`;
    const res = await fetch(url);
    const data = await res.json();

    // If any "Note", "Error Message", or "Information" field appears, throw immediately
    if (data.Note || data['Error Message'] || data.Information) {
        const msg = data.Note || data['Error Message'] || data.Information;
        throw new Error(`Alpha Vantage error: ${msg}`);
    }

    let key;
    switch (timeframe) {
        case 'intraday':
            key = 'Time Series (5min)';
            break;
        case 'weekly':
            key = 'Weekly Time Series';
            break;
        case 'monthly':
            key = 'Monthly Time Series';
            break;
        case 'daily':
        default:
            key = 'Time Series (Daily)';
            break;
    }

    if (!data[key]) {
        throw new Error('Alpha Vantage returned no data');
    }

    const series = data[key];
    const dates = Object.keys(series).reverse();
    const prices = dates.map(d => +series[d]['4. close']);
    const opens = dates.map(d => +series[d]['1. open']);
    const highs = dates.map(d => +series[d]['2. high']);
    const lows = dates.map(d => +series[d]['3. low']);
    const volumes = dates.map(d => +(series[d]['6. volume'] || series[d]['5. volume']));

    return {
        source: 'Alpha Vantage',
        symbol,
        dates,
        prices,
        opens,
        highs,
        lows,
        volumes
    };
}

/**
 * Fetches company profile/overview from Alpha Vantage free-tier endpoint.
 *
 * @param {string} symbol    // e.g. 'AAPL'
 * @returns {Promise<Object>} // overview JSON
 */
export async function fetchCompanyProfile(symbol) {
    if (!isAlphaVantageAvailable()) {
        throw new Error('Alpha Vantage rate limit exceeded. Please wait a moment and try again.');
    }

    alphaVantageCallCount++;
    const url = `https://www.alphavantage.co/query?function=OVERVIEW&symbol=${symbol}&apikey=${ALPHA_VANTAGE_API_KEY}`;
    const res = await fetch(url);
    const data = await res.json();

    if (data.Note || data['Error Message'] || data.Information) {
        const msg = data.Note || data['Error Message'] || data.Information;
        throw new Error(`Alpha Vantage error: ${msg}`);
    }
    if (!data || !Object.keys(data).length || !data.Symbol) {
        throw new Error('No company profile data available');
    }
    return data;
}
