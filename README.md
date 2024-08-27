# Run book:

1) Put the source code in the Xampp server
2) Enable xsl in php.ini
3) Install xml for php
4) Open in browser and use 

# Admin's revenue:

1) Admin takes 1% of reserve price when auction fails
2) Admin takes 3% of sale price when auction is sold

# Calculation:

## Admin:

1) Immediately charge seller 1% of reserve price when listing/selling a new item (on listing page)
2) If auction fails, will not return 1% of reserve price
3) If auction is sold, return 1% of reserve price and then charge 3% of sold price

## Seller: 

1) Since admin takes 3% of sold price, seller will receive 97% of sold price

## Bidder:

1) If auction fails, refund
2) If auction wins, lose money


# During auction:

## Bidder Charge:

1) Bidder will only be charged the difference between their last bid (0 if not bided) and bid price
2) Similar to buy now price, only be charged the difference between their last bid (0 if not bided) and buy it now price

## Bid rules:

1) The auction must be in progress
2) The bidder must not be the seller 
3) The bidder is not having the highest bid
4) The auction deadline is not passed
5) The new bid must greater than the highest bid
6) The new bid must not higher than the buy it now price
7) The balance must higher than the difference between new bid and last bid of the bidder



