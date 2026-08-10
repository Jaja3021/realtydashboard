/** @type {import('next').NextConfig} */
const nextConfig = {
  async rewrites() {
    return [
      { source: '/', destination: '/index.html' },
      { source: '/add-sale', destination: '/add-sale.html' },
      { source: '/sales', destination: '/sales.html' },
      { source: '/top-sales', destination: '/top-sales.html' },
      { source: '/calendar', destination: '/calendar.html' },
    ];
  },
};

module.exports = nextConfig;
