// ─── API SERVICE SECTION ───────────────────────

//Alpha Vantage API key (free-one)
const ALPHA_VANTAGE_API_KEY = '09SP6T4P7W333QWJ';

// (2) Rate-limit tracking (max 5 calls/minute for free tier)
let alphaVantageCallCount = 0;
let alphaVantageRateLimited = false;
let lastResetTime = Date.now();
let alphaVantageResetTimer = null;

/*Returns true if we have not exceeded 5 calls in the last rolling minute.*/
function isAlphaVantageAvailable() {
  const now = Date.now();
  if (now - lastResetTime >= 60_000) {
    alphaVantageCallCount = 0;
    lastResetTime = now;
    clearTimeout(alphaVantageResetTimer);
    alphaVantageRateLimited = false;
  }
  return !alphaVantageRateLimited && alphaVantageCallCount < 5;
}

/**
 * Mark the service as rate-limited for one minute.
 */
function setAlphaVantageRateLimited() {
  alphaVantageRateLimited = true;
  alphaVantageResetTimer = setTimeout(() => {
    alphaVantageRateLimited = false;
    alphaVantageCallCount = 0;
    lastResetTime = Date.now();
    updateApiStatusIndicator();
  }, 60_000);
  updateApiStatusIndicator();
}

/**
 * Fetches stock time series from Alpha Vantage (free endpoints only).
 * Dynamically finds the "Time Series" key in the JSON response.
 */
async function fetchStockTimeSeries(symbol, timeframe = 'daily') {
  if (!isAlphaVantageAvailable()) {
    throw new Error('Alpha Vantage rate limit exceeded. Please wait a moment and try again.');
  }

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
      // Switched to free endpoint
      functionName = 'TIME_SERIES_DAILY';
      break;
  }

  const url = `https://www.alphavantage.co/query?function=${functionName}&symbol=${symbol}${intervalParam}&outputsize=compact&apikey=${ALPHA_VANTAGE_API_KEY}`;
  console.log('⮕ Alpha Vantage URL:', url);
  const res = await fetch(url);
  const data = await res.json();

  // If API returns "Information" field or a "Note"/"Error Message," bail out
  if (data.Information || data.Note || data['Error Message']) {
    const msg = data.Information || data.Note || data['Error Message'];
    if (msg.includes('premium') || msg.includes('rate limit')) {
      setAlphaVantageRateLimited();
    }
    throw new Error(`Alpha Vantage error: ${msg}`);
  }

  // Dynamically find whichever key contains "Time Series"
  const timeSeriesKey = Object.keys(data).find(key => key.includes('Time Series'));
  if (!timeSeriesKey) {
    throw new Error('No time series data found');
  }

  const series = data[timeSeriesKey];
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
 * Fetches company overview/profile from Alpha Vantage.
 */
async function fetchCompanyProfile(symbol) {
  if (!isAlphaVantageAvailable()) {
    throw new Error('Alpha Vantage rate limit exceeded. Please wait a moment and try again.');
  }
  alphaVantageCallCount++;

  const url = `https://www.alphavantage.co/query?function=OVERVIEW&symbol=${symbol}&apikey=${ALPHA_VANTAGE_API_KEY}`;
  const res = await fetch(url);
  const data = await res.json();

  if (data.Note || data['Error Message'] || data.Information) {
    const msg = data.Note || data['Error Message'] || data.Information;
    if (msg.includes('rate limit')) {
      setAlphaVantageRateLimited();
    }
    throw new Error(`Alpha Vantage error: ${msg}`);
  }
  if (!data || !data.Symbol) {
    throw new Error('No company profile data available');
  }
  return data;
}

// ─── END API SERVICE SECTION ──────────────────────────────────────────────────────

// ─── UI & CHART LOGIC SECTION ──────────────────────────────────────────────────────

// DOM elements
const symbolInput = document.getElementById('symbolInput');
const searchBtn = document.getElementById('searchBtn');
const maBtn = document.getElementById('maBtn');
const rsiBtn = document.getElementById('rsiBtn');
const macdBtn = document.getElementById('macdBtn');
const predictBtn = document.getElementById('predictBtn');
const annotateBtn = document.getElementById('annotateBtn');
const drawLineBtn = document.getElementById('drawLineBtn');

// Chart & data state
let stockChart;
let stockData = {
  symbol: '',
  dates: [],
  prices: [],
  opens: [],
  highs: [],
  lows: [],
  volumes: []
};
let maData = [], rsiData = [], macdLine = [], signalLine = [], predictionPoint = null;
let noteCount = 0;
let indicatorsVisible = { ma: false, rsi: false, macd: false, prediction: false };

// Drawing state
let isDrawingLine = false;
let lineStart = null;

// Status message element for user feedback
const statusMessage = document.createElement('div');
statusMessage.className = 'status-message';
document.querySelector('.container').insertBefore(statusMessage, document.querySelector('.search-bar'));

// When DOM is ready, wire up event listeners
document.addEventListener('DOMContentLoaded', () => {
  searchBtn.addEventListener('click', fetchStockData);
  symbolInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') fetchStockData();
  });
  maBtn.addEventListener('click', toggleMA);
  rsiBtn.addEventListener('click', toggleRSI);
  macdBtn.addEventListener('click', toggleMACD);
  predictBtn.addEventListener('click', togglePrediction);
  annotateBtn.addEventListener('click', addNOTE);
  drawLineBtn.addEventListener('click', toggleDrawingMode);

  addCompanySearch();
  addApiStatusIndicator();
});

/**
 * Toggle drawing mode for trend lines
 */
function toggleDrawingMode() {
  isDrawingLine = !isDrawingLine;
  drawLineBtn.classList.toggle('active', isDrawingLine);
  
  if (stockChart) {
    stockChart.canvas.style.cursor = isDrawingLine ? 'crosshair' : 'default';
  }
  
  if (isDrawingLine) {
    showStatus('Click and drag to draw trend line', 'info');
  } else {
    showStatus('Drawing mode disabled', 'info');
    // Clean up any temporary lines when disabling drawing mode
    if (stockChart && stockChart.options.plugins.annotation.annotations['tempLine']) {
      delete stockChart.options.plugins.annotation.annotations['tempLine'];
      stockChart.update();
    }
  }
}

/**
 * Adds a small "Alpha Vantage" status dot (green if available, red if rate-limited).
 */
function addApiStatusIndicator() {
  const apiStatus = document.createElement('div');
  apiStatus.className = 'api-status';
  apiStatus.innerHTML = `
    <div class="api-indicator">
      <span class="api-label">Alpha Vantage:</span>
      <span class="api-status-light" id="alphaVantageStatus"></span>
    </div>
  `;
  document.querySelector('.controls').insertAdjacentElement('afterend', apiStatus);

  const footer = document.createElement('footer');
  footer.innerHTML = `
    <p>Stock Analysis Web Application for Analytic Insight and Prediction</p>
    <p class="api-info">Using Alpha Vantage API for reliable stock data</p>
  `;
  document.querySelector('.container').appendChild(footer);

  updateApiStatusIndicator();
}

/**
 * Refreshes the little colored dot:
 * - green ("active") if not rate-limited
 * - red ("limited") if currently blocked
 */
function updateApiStatusIndicator() {
  const alphaStatus = document.getElementById('alphaVantageStatus');
  if (!alphaStatus) return;
  alphaStatus.className = 'api-status-light' + (alphaVantageRateLimited ? ' limited' : ' active');
}

/**
 * Adds a live "search by company name" dropdown under the symbol box.
 */
function addCompanySearch() {
  const searchResults = document.createElement('div');
  searchResults.className = 'search-results';
  searchResults.style.display = 'none';
  document.querySelector('.search-bar').appendChild(searchResults);

  let debounceTimer;
  symbolInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async () => {
      const query = symbolInput.value.trim();
      if (query.length < 2) {
        searchResults.style.display = 'none';
        return;
      }
      try {
        if (!isAlphaVantageAvailable()) {
          throw new Error('Rate limit exceeded');
        }
        alphaVantageCallCount++;
        const url = `https://www.alphavantage.co/query?function=SYMBOL_SEARCH&keywords=${query}&apikey=${ALPHA_VANTAGE_API_KEY}`;
        const response = await fetch(url);
        const data = await response.json();
        if (data.Note || data['Error Message'] || data.Information) {
          const msg = data.Note || data['Error Message'] || data.Information;
          if (msg.includes('rate limit')) setAlphaVantageRateLimited();
          throw new Error(msg);
        }
        if (!data.bestMatches) {
          throw new Error('No search results');
        }
        const matches = data.bestMatches.map(m => ({
          symbol: m['1. symbol'],
          name:   m['2. name'],
          region: m['4. region']
        }));
        displaySearchResults(matches, searchResults);
      } catch (err) {
        console.error('Search error:', err);
        showStatus(`Search error: ${err.message}`, 'error');
      }
    }, 500);
  });

  document.addEventListener('click', (e) => {
    if (!symbolInput.contains(e.target) && !searchResults.contains(e.target)) {
      searchResults.style.display = 'none';
    }
  });
}

/**
 * Populates the dropdown with clickable items.
 */
function displaySearchResults(results, container) {
  container.innerHTML = '';
  if (results.length === 0) {
    container.style.display = 'none';
    return;
  }
  results.forEach(item => {
    const resultItem = document.createElement('div');
    resultItem.className = 'search-result-item';
    resultItem.innerHTML = `
      <div class="symbol">${item.symbol}</div>
      <div class="name">${item.name}</div>
      <div class="region">${item.region}</div>
    `;
    resultItem.addEventListener('click', () => {
      symbolInput.value = item.symbol;
      container.style.display = 'none';
      fetchStockData();
    });
    container.appendChild(resultItem);
  });
  container.style.display = 'block';
}

/**
 * Shows a brief status banner (info/success/error) below the search bar.
 */
function showStatus(message, type = 'info') {
  statusMessage.textContent = message;
  statusMessage.className = `status-message ${type}`;
  statusMessage.style.display = 'block';
  if (type !== 'error') {
    setTimeout(() => {
      statusMessage.style.display = 'none';
    }, 5000);
  }
}

/**
 * Core fetch/processing routine:  
 * 1) Validate symbol  
 * 2) Call fetchStockTimeSeries(...)  
 * 3) Compute indicators & render chart  
 * 4) Optionally fetch & display company overview  
 */
async function fetchStockData() {
  const symbol = symbolInput.value.trim().toUpperCase();
  if (!symbol) {
    showStatus('Please enter a stock symbol.', 'error');
    return;
  }
  showStatus(`Fetching data for ${symbol}...`, 'info');

  try {
    resetIndicators();
    // TIME SERIES (daily)
    const result = await fetchStockTimeSeries(symbol, 'daily');
    stockData = {
      symbol: result.symbol,
      dates: result.dates,
      prices: result.prices,
      opens: result.opens,
      highs: result.highs,
      lows: result.lows,
      volumes: result.volumes
    };
    showStatus('Data loaded successfully from Alpha Vantage', 'success');

    computeMA(20);
    computeRSI(14);
    computeMACD(12, 26, 9);
    renderChart();

    // Optional: fetch overview
    try {
      const overview = await fetchCompanyProfile(symbol);
      displayCompanyInfo(overview);
    } catch (err) {
      console.log('Overview error:', err);
      // Show a more informative message about company info not being available
      let companyInfo = document.querySelector('.company-info');
      if (!companyInfo) {
        companyInfo = document.createElement('div');
        companyInfo.className = 'company-info';
        document.querySelector('.container').insertBefore(
          companyInfo,
          document.getElementById('stockChart')
        );
      }
      companyInfo.innerHTML = `
        <div class="company-header">
          <div class="company-name-container">
            <h2>${symbol}</h2>
            <p>Company Information</p>
          </div>
        </div>
        <div class="company-details">
          <p>
            <strong>Note:</strong> Detailed company information is not available in the free tier of Alpha Vantage API. 
            The chart data and technical indicators are still available for analysis.
          </p>
        </div>
      `;
    }
  } catch (err) {
    console.error('Error fetching stock data:', err);
    showStatus(`Error: ${err.message}`, 'error');
  }
}

/**
 * Display company overview/overview in a section below the chart.
 */
function displayCompanyInfo(overview) {
  let companyInfo = document.querySelector('.company-info');
  if (!companyInfo) {
    companyInfo = document.createElement('div');
    companyInfo.className = 'company-info';
    document.querySelector('.container').insertBefore(
      companyInfo,
      document.getElementById('stockChart')
    );
  }
  companyInfo.innerHTML = `
    <div class="company-header">
      <div class="company-name-container">
        <h2>${overview.Name} (${overview.Symbol})</h2>
        <p>${overview.Exchange} | ${overview.Currency}</p>
      </div>
    </div>
    <div class="company-details">
      <p>
        <strong>Sector:</strong> ${overview.Sector}<br>
        <strong>Industry:</strong> ${overview.Industry}<br>
        <strong>Market Cap:</strong> ${
          overview.MarketCapitalization
            ? '$' + (overview.MarketCapitalization / 1e6).toFixed(0) + 'M'
            : 'N/A'
        }<br>
        <strong>P/E Ratio:</strong> ${overview.PERatio || 'N/A'}<br>
        <strong>Description:</strong> ${
          overview.Description
            ? overview.Description.substring(0, 200) + '...'
            : 'No description available'
        }
      </p>
    </div>
  `;
}

// ─── ZOOM PLUGIN REGISTRATION ───────────────────────────────────────────────────

Chart.register(ChartZoom);

/**
 * Reset indicator flags and remove any extra UI (charts/annotations).
 */
function resetIndicators() {
  indicatorsVisible = { ma: false, rsi: false, macd: false, prediction: false };
  [maBtn, rsiBtn, macdBtn, predictBtn].forEach(btn => btn.classList.remove('active'));
  predictionPoint = null;

  // Remove any RSI/MACD canvases if they were open
  const rsiCanvas = document.getElementById('rsiChart');
  if (rsiCanvas) rsiCanvas.remove();
  const macdCanvas = document.getElementById('macdChart');
  if (macdCanvas) macdCanvas.remove();
  const predictionInfo = document.getElementById('predictionInfo');
  if (predictionInfo) predictionInfo.remove();
}

/**
 * Calculate 20-day Simple Moving Average (SMA).
 */
function computeMA(period) {
  maData = stockData.prices.map((_, i, arr) => {
    if (i < period - 1) return null;
    return arr.slice(i - period + 1, i + 1).reduce((a, b) => a + b, 0) / period;
  });
}

/**
 * Calculate 14-day Relative Strength Index (RSI).
 */
function computeRSI(period) {
  const gains = [], losses = [];
  stockData.prices.forEach((price, i, arr) => {
    if (i === 0) {
      gains.push(0);
      losses.push(0);
      return;
    }
    const diff = price - arr[i - 1];
    gains.push(diff > 0 ? diff : 0);
    losses.push(diff < 0 ? Math.abs(diff) : 0);
  });

  rsiData = gains.map((_, i) => {
    if (i < period) return null;
    const avgGain = gains.slice(i - period + 1, i + 1).reduce((a, b) => a + b, 0) / period;
    const avgLoss = losses.slice(i - period + 1, i + 1).reduce((a, b) => a + b, 0) / period;
    const rs = avgGain / (avgLoss || 1);
    return 100 - (100 / (1 + rs));
  });
}

/**
 * Helper to compute Exponential Moving Average (EMA).
 */
function computeEMA(period, data) {
  const k = 2 / (period + 1);
  return data.map((val, i, arr) => {
    if (i === 0) return val;
    return val * k + arr[i - 1] * (1 - k);
  });
}

/**
 * Calculate MACD line and signal line.
 */
function computeMACD(fast, slow, signal) {
  const emaFast = computeEMA(fast, stockData.prices);
  const emaSlow = computeEMA(slow, stockData.prices);
  macdLine = emaFast.map((v, i) => v - emaSlow[i]);
  signalLine = computeEMA(signal, macdLine);
}

/**
 * Compute linear regression (slope, intercept) on (index, price).
 */
function computeLinearRegression(x, y) {
  const n = x.length;
  const sumX = x.reduce((a, b) => a + b, 0);
  const sumY = y.reduce((a, b) => a + b, 0);
  const sumXY = x.map((v, i) => v * y[i]).reduce((a, b) => a + b, 0);
  const sumXX = x.map(v => v * v).reduce((a, b) => a + b, 0);
  const slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
  const intercept = (sumY - slope * sumX) / n;
  return { slope, intercept };
}

/**
 * Toggle 20-day moving average on the main chart.
 */
function toggleMA() {
  if (!stockChart) return;
  indicatorsVisible.ma = !indicatorsVisible.ma;
  maBtn.classList.toggle('active', indicatorsVisible.ma);

  if (indicatorsVisible.ma) {
    stockChart.data.datasets.push({
      label: '20-Day MA',
      data: maData,
      borderColor: 'rgba(255, 99, 132, 1)',
      borderWidth: 1.5,
      pointRadius: 0,
      fill: false
    });
  } else {
    stockChart.data.datasets = stockChart.data.datasets.filter(d => d.label !== '20-Day MA');
  }
  stockChart.update();
}

/**
 * Toggle a separate RSI chart below the main price chart.
 */
function toggleRSI() {
  if (!stockChart) return;
  indicatorsVisible.rsi = !indicatorsVisible.rsi;
  rsiBtn.classList.toggle('active', indicatorsVisible.rsi);

  if (indicatorsVisible.rsi) {
    let rsiCanvas = document.getElementById('rsiChart');
    if (!rsiCanvas) {
      rsiCanvas = document.createElement('canvas');
      rsiCanvas.id = 'rsiChart';
      document.getElementById('stockChart').insertAdjacentElement('afterend', rsiCanvas);
    }

    const rsiCtx = rsiCanvas.getContext('2d');
    new Chart(rsiCtx, {
      type: 'line',
      data: {
        labels: stockData.dates,
        datasets: [{
          label: 'RSI (14)',
          data: rsiData,
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1.5,
          pointRadius: 0,
          fill: false
        }]
      },
      options: {
        scales: {
          x: { display: true },
          y: {
            display: true,
            min: 0,
            max: 100,
            grid: {
              color: (ctx) => {
                if (ctx.tick.value === 30 || ctx.tick.value === 70) {
                  return 'rgba(255, 0, 0, 0.3)';
                }
                return 'rgba(0, 0, 0, 0.1)';
              }
            }
          }
        },
        plugins: {
          annotation: {
            annotations: {
              overbought: {
                type: 'line',
                yMin: 70,
                yMax: 70,
                borderColor: 'rgba(255, 0, 0, 0.5)',
                borderWidth: 1,
                label: {
                  enabled: true,
                  content: 'Overbought',
                  position: 'start'
                }
              },
              oversold: {
                type: 'line',
                yMin: 30,
                yMax: 30,
                borderColor: 'rgba(255, 0, 0, 0.5)',
                borderWidth: 1,
                label: {
                  enabled: true,
                  content: 'Oversold',
                  position: 'start'
                }
              }
            }
          }
        }
      }
    });
  } else {
    const rsiCanvas = document.getElementById('rsiChart');
    if (rsiCanvas) rsiCanvas.remove();
  }
}

/**
 * Toggle a separate MACD chart below RSI (or below main if RSI is hidden).
 */
function toggleMACD() {
  if (!stockChart) return;
  indicatorsVisible.macd = !indicatorsVisible.macd;
  macdBtn.classList.toggle('active', indicatorsVisible.macd);

  if (indicatorsVisible.macd) {
    let macdCanvas = document.getElementById('macdChart');
    if (!macdCanvas) {
      macdCanvas = document.createElement('canvas');
      macdCanvas.id = 'macdChart';
      const rsiCanvas = document.getElementById('rsiChart');
      if (rsiCanvas) {
        rsiCanvas.insertAdjacentElement('afterend', macdCanvas);
      } else {
        document.getElementById('stockChart').insertAdjacentElement('afterend', macdCanvas);
      }
    }

    const macdCtx = macdCanvas.getContext('2d');
    new Chart(macdCtx, {
      type: 'line',
      data: {
        labels: stockData.dates,
        datasets: [
          {
            label: 'MACD Line',
            data: macdLine,
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1.5,
            pointRadius: 0,
            fill: false
          },
          {
            label: 'Signal Line',
            data: signalLine,
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1.5,
            pointRadius: 0,
            fill: false
          }
        ]
      },
      options: {
        scales: {
          x: { display: true },
          y: { display: true }
        }
      }
    });
  } else {
    const macdCanvas = document.getElementById('macdChart');
    if (macdCanvas) macdCanvas.remove();
  }
}

/**
 * Toggle a linear regression price prediction line onto the main chart.
 */
function togglePrediction() {
  if (!stockChart) return;
  indicatorsVisible.prediction = !indicatorsVisible.prediction;
  predictBtn.classList.toggle('active', indicatorsVisible.prediction);

  if (indicatorsVisible.prediction) {
    const { slope, intercept } = computeLinearRegression(
      stockData.prices.map((_, i) => i),
      stockData.prices
    );

    const lastIndex = stockData.prices.length - 1;
    const predictions = [];
    const predictionDates = [];
    for (let i = 1; i <= 5; i++) {
      const nextIndex = lastIndex + i;
      const predicted = slope * nextIndex + intercept;
      predictions.push(predicted);

      const lastDate = new Date(stockData.dates[lastIndex]);
      lastDate.setDate(lastDate.getDate() + i);
      predictionDates.push(lastDate.toISOString().split('T')[0]);
    }

    stockChart.data.labels = [...stockData.dates, ...predictionDates];
    stockChart.data.datasets.push({
      label: 'Prediction',
      data: [...stockData.prices, ...predictions],
      borderColor: 'rgba(153, 102, 255, 1)',
      borderWidth: 2,
      borderDash: [5, 5],
      pointRadius: (ctx) => ctx.dataIndex > lastIndex ? 4 : 0,
      pointBackgroundColor: 'rgba(153, 102, 255, 1)',
      fill: false
    });

    const predictionInfo = document.createElement('div');
    predictionInfo.id = 'predictionInfo';
    predictionInfo.className = 'prediction-info';
    predictionInfo.innerHTML = `
      <h3>Price Prediction (Linear Regression)</h3>
      <p>Trend: ${slope > 0 ? 'Upward ↗' : 'Downward ↘'}</p>
      <p>Predicted in 5 days: $${predictions[4].toFixed(2)}</p>
      <p>Change: ${((predictions[4] - stockData.prices[lastIndex]) / stockData.prices[lastIndex] * 100).toFixed(2)}%</p>
    `;
    document.querySelector('.controls').insertAdjacentElement('afterend', predictionInfo);
  } else {
    stockChart.data.labels = stockData.dates;
    stockChart.data.datasets = stockChart.data.datasets.filter(d => d.label !== 'Prediction');
    const predictionInfo = document.getElementById('predictionInfo');
    if (predictionInfo) predictionInfo.remove();
  }

  stockChart.update();
}

/**
 * Add a point annotation (buy/sell/note) onto the chart at a selected date.
 */
function addNOTE() {
  if (!stockChart) return;

  const modal = document.createElement('div');
  modal.className = 'annotation-modal';
  modal.innerHTML = `
    <div class="annotation-modal-content">
      <h3>Add Chart Annotation</h3>
      <div class="form-group">
        <label for="annotationDate">Date:</label>
        <select id="annotationDate">
          ${stockData.dates.map((date, i) =>
            `<option value="${date}" ${i === stockData.dates.length - 1 ? 'selected' : ''}>${date}</option>`
          ).join('')}
        </select>
      </div>
      <div class="form-group">
        <label for="annotationType">Type:</label>
        <select id="annotationType">
          <option value="buy">Buy Signal</option>
          <option value="sell">Sell Signal</option>
          <option value="note">Note</option>
        </select>
      </div>
      <div class="form-group">
        <label for="annotationText">Text:</label>
        <input type="text" id="annotationText" placeholder="Enter annotation text">
      </div>
      <div class="modal-buttons">
        <button id="cancelAnnotation">Cancel</button>
        <button id="saveAnnotation">Save</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);

  document.getElementById('cancelAnnotation').addEventListener('click', () => {
    modal.remove();
  });
  document.getElementById('saveAnnotation').addEventListener('click', () => {
    const date = document.getElementById('annotationDate').value;
    const type = document.getElementById('annotationType').value;
    const text = document.getElementById('annotationText').value || type;
    if (!date) return;

    const dateIndex = stockData.dates.indexOf(date);
    const price = stockData.prices[dateIndex];

    let backgroundColor, borderColor;
    if (type === 'buy') {
      backgroundColor = 'rgba(0, 255, 0, 0.7)';
      borderColor = 'green';
    } else if (type === 'sell') {
      backgroundColor = 'rgba(255, 0, 0, 0.7)';
      borderColor = 'red';
    } else {
      backgroundColor = 'rgba(255, 255, 0, 0.7)';
      borderColor = 'orange';
    }

    const id = 'note' + noteCount++;
    stockChart.options.plugins.annotation.annotations[id] = {
      type: 'point',
      xValue: date,
      yValue: price,
      radius: 6,
      backgroundColor,
      borderColor,
      borderWidth: 2,
      label: {
        enabled: true,
        content: text,
        position: 'center',
        backgroundColor,
        font: { size: 12 }
      }
    };
    stockChart.update();
    modal.remove();
  });
}

/**
 * Render or re-render the main line chart of price vs. date.
 */
function renderChart() {
  const ctx = document.getElementById('stockChart').getContext('2d');
  if (stockChart) {
    stockChart.destroy();
  }
  stockChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: stockData.dates,
      datasets: [{
        label: `${stockData.symbol} Price`,
        data: stockData.prices,
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 2,
        pointRadius: 0,
        fill: false
      }]
    },
    options: {
      plugins: {
        zoom: {
          pan: { enabled: true, mode: 'x', threshold: 10 },
          zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: 'x' }
        },
        annotation: {
          annotations: {}
        }
      },
      scales: { x: { display: true }, y: { display: true } }
    }
  });

  // Drawing event handlers
  const canvas = stockChart.canvas;
  let currentLine = null;
  let isDragging = false;

  function getChartCoordinates(evt) {
    const rect = canvas.getBoundingClientRect();
    const xScale = stockChart.scales.x;
    const yScale = stockChart.scales.y;
    
    // Get mouse position relative to canvas
    const x = evt.clientX - rect.left;
    const y = evt.clientY - rect.top;
    
    // Convert to chart data values
    const xValue = xScale.getValueForPixel(x);
    const yValue = yScale.getValueForPixel(y);
    
    // Find the nearest data point
    const nearestIndex = stockData.dates.reduce((nearest, date, index) => {
      const currentDate = new Date(date);
      const targetDate = new Date(xValue);
      const currentDiff = Math.abs(currentDate - targetDate);
      const nearestDiff = Math.abs(new Date(stockData.dates[nearest]) - targetDate);
      return currentDiff < nearestDiff ? index : nearest;
    }, 0);
    
    return {
      xValue: stockData.dates[nearestIndex],
      yValue: stockData.prices[nearestIndex]
    };
  }

  canvas.onmousedown = function (evt) {
    if (!isDrawingLine) return;
    
    const { xValue, yValue } = getChartCoordinates(evt);
    lineStart = { x: xValue, y: yValue };
    isDragging = true;

    // Create temporary line for preview
    const id = 'tempLine';
    currentLine = {
      type: 'line',
      xMin: xValue,
      xMax: xValue,
      yMin: yValue,
      yMax: yValue,
      borderColor: 'rgba(255, 99, 71, 0.8)',
      borderWidth: 2,
      borderDash: [5, 5]
    };
    stockChart.options.plugins.annotation.annotations[id] = currentLine;
    stockChart.update();
  };

  canvas.onmousemove = function (evt) {
    if (!isDrawingLine || !isDragging || !lineStart) return;
    
    const { xValue, yValue } = getChartCoordinates(evt);
    
    // Update temporary line
    if (currentLine) {
      currentLine.xMax = xValue;
      currentLine.yMax = yValue;
      
      // Calculate and display the angle
      const startIndex = stockData.dates.indexOf(lineStart.x);
      const endIndex = stockData.dates.indexOf(xValue);
      const dx = endIndex - startIndex;
      const dy = yValue - lineStart.y;
      const angle = Math.atan2(dy, dx) * (180 / Math.PI);
      
      stockChart.update();
    }
  };

  canvas.onmouseup = function (evt) {
    if (!isDrawingLine || !isDragging || !lineStart) return;
    
    const { xValue, yValue } = getChartCoordinates(evt);
    isDragging = false;
    
    // Only create a permanent line if the start and end points are different
    if (xValue !== lineStart.x) {
      // Remove temporary line
      delete stockChart.options.plugins.annotation.annotations['tempLine'];
      
      // Calculate the angle for the permanent line
      const startIndex = stockData.dates.indexOf(lineStart.x);
      const endIndex = stockData.dates.indexOf(xValue);
      const dx = endIndex - startIndex;
      const dy = yValue - lineStart.y;
      const angle = Math.atan2(dy, dx) * (180 / Math.PI);
      
      // Add permanent trend line annotation
      const id = 'trendLine_' + Date.now();
      stockChart.options.plugins.annotation.annotations[id] = {
        type: 'line',
        xMin: lineStart.x,
        xMax: xValue,
        yMin: lineStart.y,
        yMax: yValue,
        borderColor: 'rgba(255, 99, 71, 0.8)',
        borderWidth: 2,
        borderDash: [5, 5]
      };
      
      stockChart.update();
    } else {
      // If start and end points are the same, just remove the temporary line
      delete stockChart.options.plugins.annotation.annotations['tempLine'];
      stockChart.update();
    }
    
    lineStart = null;
    currentLine = null;
  };

  // Add mouseout handler to clean up if mouse leaves canvas while drawing
  canvas.onmouseout = function() {
    if (isDrawingLine && isDragging) {
      isDragging = false;
      delete stockChart.options.plugins.annotation.annotations['tempLine'];
      stockChart.update();
      lineStart = null;
      currentLine = null;
    }
  };

  // Clean up event handlers when chart is destroyed
  const originalDestroy = stockChart.destroy;
  stockChart.destroy = function() {
    canvas.onmousedown = null;
    canvas.onmousemove = null;
    canvas.onmouseup = null;
    canvas.onmouseout = null;
    originalDestroy.apply(this, arguments);
  };
}