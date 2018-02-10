<?php
/**
 * @author JKetelaar
 */

namespace JKetelaar\fut\api\config;

abstract class URL {
    const REFERER = 'https://www.easports.com/fifa/ultimate-team/web-app/';

    // Login URLs
    const LOGIN_MAIN     = 'https://www.easports.com/fifa/ultimate-team/web-app';
    const LOGIN_NUCLEUS  = 'https://www.easports.com/iframe/fut17/?locale=en_US&baseShowoffUrl=https%3A%2F%2Fwww.easports.com%2Fde%2Ffifa%2Fultimate-team%2Fweb-app%2Fshow-off&guest_app_uri=http%3A%2F%2Fwww.easports.com%2Fde%2Ffifa%2Fultimate-team%2Fweb-app';
    const LOGIN_PERSONAS = 'https://www.easports.com/fifa/api/personas';
    const LOGIN_SHARDS   = 'https://utas.mob.v4.fut.ea.com/ut/shards/v2';
    const LOGIN_ACCOUNTS = 'https://utas.external.s2.fut.ea.com/ut/game/fifa18/user/accountinfo?filterConsoleLogin=true&sku=FUT17WEB&returningUserGameYear=2016&_=';
    const LOGIN_SESSION  = 'https://utas.external.s2.fut.ea.com/ut/auth';
    const LOGIN_QUESTION = 'https://utas.external.s2.fut.ea.com/ut/game/fifa18/phishing/question?_=';
    const LOGIN_VALIDATE = 'https://utas.external.s2.fut.ea.com/ut/game/fifa18/phishing/validate?_=';

    // API Endpoints
    const API_CREDITS               = '/ut/game/fifa18/user/credits';
    const API_TRADEPILE             = '/ut/game/fifa18/tradepile';
    const API_REMOVE_FROM_TRADEPILE = '/ut/game/fifa18/trade/%s'; // Replaceable %s
    const API_WATCHLIST             = '/ut/game/fifa18/watchlist';
    const API_PILESIZE              = '/ut/game/fifa18/clientdata/pileSize';
    const API_RELIST                = '/ut/game/fifa18/auctionhouse/relist';
    const API_TRANSFER_MARKET       = '/ut/game/fifa18/transfermarket?';
    const API_PLACE_BID             = '/ut/game/fifa18/trade/%s/bid'; // Replaceable %s
    const API_LIST_ITEM             = '/ut/game/fifa18/auctionhouse';
    const API_STATUS                = '/ut/game/fifa18/trade/status?';
    const API_ITEM                  = '/ut/game/fifa18/item';
    const API_DEF                   = '/ut/game/fifa18/defid?type=player&count=35&start=0&defId=%s'; // Replaceable %s

    // Players endpoints
    const PLAYERS_DATABASE = 'https://www.easports.com/fifa/ultimate-team/web-app/content/B1BA185F-AD7C-4128-8A64-746DE4EC5A82/2018/fut/items/web/players_meta.json';
    const PLAYER_IMAGE     = 'https://www.easports.com/fifa/ultimate-team/web-app/content/B1BA185F-AD7C-4128-8A64-746DE4EC5A82/2018/fut/items/images/players/html5/120x120/%s.png'; // Replaceable %s
}
