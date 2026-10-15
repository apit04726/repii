# InstaGo — સિસ્ટમ સેટઅપ અને ચેટ બેકઅપ માર્ગદર્શિકા (Setup & Chat Backup Guide)
> **તારીખ:** 14 ઓક્ટોબર 2026  
> **પ્રોજેક્ટ પાથ:** `d:\wamp64\www\web\repii\`  
> **Reference સાઇટ:** [indown.io](https://indown.io)

---

## 📌 ૧. પ્રોજેક્ટમાં થયેલા મુખ્ય ફેરફારો (Work Completed)

1. **ઇન્ટરફેસ (UI) ભાષા સુધારો:**
   - સાઇટના તમામ લોડર ટેક્સ્ટ, બટનો, એલર્ટ મેસેજ અને વેલિડેશનને પ્રોફેશનલ અંગ્રેજીમાં કન્વર્ટ કરવામાં આવ્યા.
2. **HD સ્ટોરી ડાઉનલોડર (Real MP4 & High-Res JPG):**
   - Profile Stories, Highlights અને Specific Story Links માટે 100% ઓરિજિનલ Meta CDN વિડીયો સ્ટ્રીમ લિંક્સ સેટ કરવામાં આવી.
3. **HD પ્રોફાઇલ DP ડાઉનલોડર:**
   - કોઈપણ Instagram યુઝરનું ઓરિજિનલ 1080p ક્વોલિટી DP અને તે યુઝરની એક્ટિવ સ્ટોરીઝ એકસાથે ફેચ થાય છે.
4. **indown.io સ્ટાઇલ Session Pool (Multi-Account Failover):**
   - `config.php` માં એકથી વધુ Dummy Accounts ઉમેરવાની વ્યવસ્થા કરવામાં આવી. જો એક એકાઉન્ટ લિમિટ પર પહોંચે તો સિસ્ટમ આપોઆપ બીજા એકાઉન્ટ પર સ્વિચ થઈ જાય છે.
5. **Direct Binary Download Proxy (`download.php`):**
   - Instagram CDN લિંક્સ સીધી `Content-Disposition: attachment` સાથે ડાઉનલોડ થાય છે જેથી મોબાઈલ અને કમ્પ્યુટરમાં 1-ક્લિકમાં ફાઈલ સેવ થાય છે.
6. **RapidAPI Adapter (Zero-Maintenance Fallback):**
   - ભવિષ્યમાં હાઈ-ટ્રાફિક માટે RapidAPI ની કી નાખવાથી 100% ઓટોમેટિક ચાલે તેવી સિસ્ટમ તૈયાર કરી છે.

---

## 🔐 ૨. હાલમાં સેટ કરેલા Dummy Accounts (`config.php`)

| ક્રમ | Username / ID | User ID (`ds_user_id`) | સ્ટેટસ |
|:---:|:---|:---:|:---:|
| **Account 1 (Primary)** | `@ronnyshahh` | `38251868688` | ✅ **ACTIVE (HTTP 200)** |
| **Account 2 (Backup)** | Backup Dummy | `22996724939` | ✅ **ACTIVE (HTTP 200)** |

- બંને એકાઉન્ટ લાઈવ ટેસ્ટમાં સફળતાપૂર્વક કામ કરી રહ્યા છે.

---

## 🔒 ૩. પ્રાઇવેટ એકાઉન્ટ (Private Account) માટેના નિયમો

Instagram ના સર્વર-સાઇડ સિક્યોરિટી નિયમો અનુસાર:
- પ્રાઇવેટ એકાઉન્ટ (જેમ કે `@ishasingh_20`) નો ડેટા પબ્લિકલી કોઈ સાઇટ જોઈ શકતી નથી.
- **ડાઉનલોડ કરવાની રીત:**
  1. તમારા Dummy Account (`@ronnyshahh`) માંથી તે પ્રાઇવેટ એકાઉન્ટને **Follow Request** મોકલો.
  2. સામેવાળું એકાઉન્ટ Request Accept કરી લે એટલે આપણી સિસ્ટમ તેના બધા પ્રાઇવેટ ફોટા, રીલ્સ અને સ્ટોરી ઓટોમેટિક ડાઉનલોડ કરી શકશે.

---

## 🍪 ૪. ભવિષ્યમાં નવું Account ઉમેરવાની રીત (Cookie Extraction)

જો ભવિષ્યમાં કોઈ એકાઉન્ટનું સેશન બદલવું હોય અથવા 3rd એકાઉન્ટ ઉમેરવું હોય:
1. બ્રાઉઝરમાં Chrome Extension **Cookie-Editor** ઇન્સ્ટોલ કરો.
2. Instagram.com ખોલીને Dummy Account માં લૉગિન કરો.
3. Cookie-Editor ખોલો અને આ 3 કિંમતો કોપી કરો:
   - **`sessionid`**
   - **`ds_user_id`**
   - **`csrftoken`**
4. `config.php` માં જઈને `INSTAGRAM_SESSION_POOL` એરે (Array) માં ઉમેરી દો:
```php
define('INSTAGRAM_SESSION_POOL', [
    [
        'session_id' => 'તમારું_SESSION_ID',
        'csrf_token' => 'તમારું_CSRF_TOKEN',
        'ds_user_id' => 'તમારું_USER_ID'
    ],
    // અન્ય એકાઉન્ટ્સ...
]);
```

> **મહત્વનું:** ક્યારેય Instagram પરથી **"Log Out"** ન કરવું. એકાઉન્ટ સ્વિચ કરવા માટે **"Switch Account"** અથવા **Incognito Window / અલગ Profile** નો ઉપયોગ કરવો.

---

## 🚀 ૫. રોજના 5,000 થી 10,000 યુઝર્સ માટે ફ્રીમાં ચલાવવાની રીત

- **Direct Scrapers:** આપણી સાઇટ પબ્લિક રીલ્સ અને DP સીધા Instagram Embed / Search API દ્વારા ફેચ કરે છે, જેના માટે કોઈ પેઇડ API ની જરૂર નથી. આ **100% ફ્રી** છે.
- **Session Rotation:** 3 થી 5 એકાઉન્ટ્સ પુલમાં રાખવાથી Instagram કોઈ એક આઈડી બ્લોક કરતું નથી.
- **Monetization (કમાણી):** ટ્રાફિક 5k–10k પહોંચે ત્યારે Google AdSense અથવા Adsterra ની એડ્સ મૂકીને મહિને ₹20,000 થી ₹50,000+ ની કમાણી કરી શકાય છે.

---

## 📁 ૬. પ્રોજેક્ટ ફાઇલોની ભૂમિકા (File Structure)

- **`config.php`**: તમામ એકાઉન્ટ કૂકીઝ, Session Pool, RapidAPI કી અને પ્રોક્સી સેટિંગ્સ.
- **`api.php`**: મુખ્ય ડાઉનલોડર એન્જિન (Reels, DP, Story, GraphQL અને Failover લોજિક).
- **`download.php`**: Meta CDN વિડીયો/ફોટોને ફોર્સ ડાઉનલોડ કરાવતો બાઈનરી સ્ટ્રીમર.
- **`index.php`**: મોર્ડન, રિસ્પોન્સિવ ગ્લાસમોર્ફિઝમ ડિઝાઇન અને ડાઉનલોડર ટેબ્સ.
- **`assets/js/app.js`**: ક્લાયન્ટ-સાઇડ AJAX કોલિંગ, એનિમેટેડ લોડર્સ અને અંગ્રેજી નોટિફિકેશન સિસ્ટમ.
