// resources/js/webauthn.js
// ========= Base64URL helpers =========
const b64urlToBuf = (b64url) => {
    // Base64URL( - _ 無し/有り ) → Base64
    const b64 = b64url.replace(/-/g, '+').replace(/_/g, '/');
    const pad = '='.repeat((4 - (b64.length % 4)) % 4);
    const bin = atob(b64 + pad);
    const buf = new ArrayBuffer(bin.length);
    const view = new Uint8Array(buf);
    for (let i = 0; i < bin.length; i++) view[i] = bin.charCodeAt(i);
    return buf;
};

const bufToB64url = (buf) => {
    const bytes = buf instanceof ArrayBuffer ? new Uint8Array(buf) : new Uint8Array(buf.buffer);
    let s = '';
    for (let i = 0; i < bytes.byteLength; i++) s += String.fromCharCode(bytes[i]);
    const b64 = btoa(s).replace(/\=+$/, '').replace(/\+/g, '-').replace(/\//g, '_');
    return b64;
};

// ========= HTTP helpers =========
const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

const get = (url) =>
    fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(async (r) => {
            if (!r.ok) throw new Error(await r.text());
            return r.json();
        });

const post = (url, body) =>
    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(body),
    }).then(async (r) => {
        if (!r.ok) throw new Error(await r.text());
        return r.json().catch(() => ({}));
    });

// ========= WebAuthn option normalizers =========
// サーバから来るJSON内の Base64URL 文字列を ArrayBuffer に置換
const normalizeCreationOptions = (o) => {
    const out = structuredClone(o);
    out.challenge = b64urlToBuf(out.challenge);
    // user.id は ArrayBuffer
    if (out.user?.id) out.user.id = b64urlToBuf(out.user.id);
    // excludeCredentials[].id
    if (Array.isArray(out.excludeCredentials)) {
        out.excludeCredentials = out.excludeCredentials.map(cred => ({
            ...cred, id: b64urlToBuf(cred.id)
        }));
    }
    return out;
};

const normalizeRequestOptions = (o) => {
    const out = structuredClone(o);
    out.challenge = b64urlToBuf(out.challenge);
    if (Array.isArray(out.allowCredentials)) {
        out.allowCredentials = out.allowCredentials.map(cred => ({
            ...cred, id: b64urlToBuf(cred.id)
        }));
    }
    return out;
};

// ========= Credential to JSON =========
const attestationToJSON = (cred) => ({
    id: cred.id,
    rawId: bufToB64url(cred.rawId),
    type: cred.type,
    response: {
        clientDataJSON: bufToB64url(cred.response.clientDataJSON),
        attestationObject: bufToB64url(cred.response.attestationObject),
    },
    clientExtensionResults: cred.getClientExtensionResults?.() || {},
    // 取得できる場合のみ（ブラウザ実装依存）
    transports: cred.response.getTransports?.() || undefined,
});

const assertionToJSON = (cred) => ({
    id: cred.id,
    rawId: bufToB64url(cred.rawId),
    type: cred.type,
    response: {
        clientDataJSON: bufToB64url(cred.response.clientDataJSON),
        authenticatorData: bufToB64url(cred.response.authenticatorData),
        signature: bufToB64url(cred.response.signature),
        userHandle: cred.response.userHandle ? bufToB64url(cred.response.userHandle) : null,
    },
    clientExtensionResults: cred.getClientExtensionResults?.() || {},
});

// ========= Registration flow (Passkey登録) =========
async function registerPasskey() {
    try {
        // 1) サーバから登録オプション取得
        //    Laravel Passkeys: GET /user/passkeys/options
        const opts = await get('/user/passkeys/options');

        // 2) オプションのBase64URL→ArrayBuffer整形
        const publicKey = normalizeCreationOptions(opts.options);

        // 3) 生体認証/セキュリティキーで鍵ペア生成
        const cred = await navigator.credentials.create({ publicKey });
        if (!cred) throw new Error('credential was null');

        // 4) サーバへ登録結果(Attestation)を送信
        const nameInput = document.getElementById('passkey-name');
        const name = (nameInput?.value?.trim()) || 'My Passkey';
        const payload = { name, credential: attestationToJSON(cred) };
        await post('/user/passkeys', payload);

        // ハッシュをセットしてからリロード → Passkeysセクションへジャンプ
        history.replaceState(null, '', '#passkeys-section');
        window.location.reload();

    } catch (e) {
        handleWebAuthnError(e, '登録に失敗しました');
    }
}

// ========= Authentication flow (パスキーでログイン) =========
let loginAbortController = null;

async function loginWithPasskey({ conditional = false } = {}) {
    if (loginAbortController) {
        loginAbortController.abort();
    }
    const currentAbortController = new AbortController();
    loginAbortController = currentAbortController;

    try {
        // 1) サーバから認証オプション取得
        const opts = await get('/passkeys/login/options');
        const publicKey = normalizeRequestOptions(opts.options);

        // 2) パスキー選択
        const getOpts = {
            publicKey,
            signal: currentAbortController.signal,
            ...(conditional && { mediation: 'conditional' }),
        };
        console.log(document);
        // sleep 3 seconds
        await new Promise(resolve => setTimeout(resolve, 500));
        // すでにログインしていたら、return
        // wire:click="logout" 属性を持つボタン要素を検索
        const isAuthenticated = document.querySelector('button[wire\\:click="logout"]');
        if (isAuthenticated) return;
        const cred = await navigator.credentials.get(getOpts);
        if (!cred) throw new Error('credential was null');

        // 3) サーバへ認証結果(Assertion)を送信
        const payload = { credential: assertionToJSON(cred) };
        await post('/passkeys/login', payload);

        // ログイン成功 → 遷移
        window.location.reload();
    } catch (e) {
        // 自身で中断した場合はエラー表示しない
        if (e.name === 'AbortError') return;
        handleWebAuthnError(e, 'ログインに失敗しました');
    } finally {
        if (loginAbortController === currentAbortController) {
            loginAbortController = null;
        }
    }
}

// ========= Conditional UI（オートフィルで即出す） =========
// ログインページ読み込み時に“パスキー候補”を自動表示したい場合
async function enableConditionalUIPrompt() {
    if (!('PublicKeyCredential' in window)) return;

    try {
        const available = await PublicKeyCredential.isConditionalMediationAvailable?.();
        if (!available) return;
        // すでにログインしているなら、return
        const isAuthenticated = true;
        if (isAuthenticated) return;
        // 条件を満たせばページロード直後にパスキーUIを出す
        loginWithPasskey({ conditional: true });
    } catch (_) {
        // 失敗は握りつぶす（未対応ブラウザなど）
    }
}

// ========= Passkey削除 =========
async function deletePasskey(id) {
    if (!confirm('このパスキーを削除しますか？')) return;
    try {
        const r = await fetch(`/user/passkeys/${id}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!r.ok) throw new Error(await r.text());
        // 該当行をDOMから除去
        document.getElementById(`passkey-row-${id}`)?.remove();
        // 残りが0件なら「まだ登録なし」メッセージを表示
        const list = document.getElementById('passkey-list');
        if (list && list.children.length === 0) {
            list.insertAdjacentHTML(
                'afterend',
                '<p class="mt-4 text-sm text-gray-500" id="passkey-empty">パスキーはまだ登録されていません。</p>'
            );
            list.remove();
        }
    } catch (e) {
        alert('削除に失敗しました: ' + (e.message || e));
    }
}
// モジュールスコープ外（インラインonclick）から呼べるようにグローバルに公開
window.deletePasskey = deletePasskey;

// ========= お行儀よくエラーを整形 =========
function handleWebAuthnError(err, fallback) {
    // 代表的ブラウザエラーをユーザー向けに言い換え
    const msg = (err?.name) ? ({
        NotAllowedError: '操作がキャンセルされました（タイムアウト/キャンセル）',
        InvalidStateError: 'このデバイス/認証器のパスキーは既に登録済みです。別のデバイスや認証器（セキュリティキーなど）で追加登録してください。',
        SecurityError: 'オリジン/HTTPS要件を満たしていません',
        ConstraintError: 'プラットフォーム要件を満たせませんでした',
        UnknownError: '認証器で予期しないエラーが発生しました'
    }[err.name] || `${fallback}: ${err.message || err}`) : `${fallback}: ${err?.message || err}`;

    console.error(err);
    alert(msg);
}

// ========= デバイス/ブラウザ名を取得 =========
function getDeviceLabel() {
    // Chrome/Edge: User-Agent Client Hints が使えれば優先
    if (navigator.userAgentData?.brands) {
        const brand = navigator.userAgentData.brands
            .filter(b => !/Not|Chromium/i.test(b.brand))
            .map(b => b.brand)[0];
        const platform = navigator.userAgentData.platform || '';
        if (brand && platform) return `${platform} ${brand}`;
        if (brand) return brand;
    }
    // Fallback: UA 文字列を解析
    const ua = navigator.userAgent;
    let browser = 'Browser';
    if (/Edg\//.test(ua))                          browser = 'Edge';
    else if (/OPR\/|Opera/.test(ua))               browser = 'Opera';
    else if (/Chrome\//.test(ua))                  browser = 'Chrome';
    else if (/Firefox\//.test(ua))                 browser = 'Firefox';
    else if (/Safari\//.test(ua))                  browser = 'Safari';
    let os = '';
    if      (/iPhone/.test(ua))  os = 'iPhone';
    else if (/iPad/.test(ua))    os = 'iPad';
    else if (/Android/.test(ua)) os = 'Android';
    else if (/Mac/.test(ua))     os = 'Mac';
    else if (/Windows/.test(ua)) os = 'Windows';
    else if (/Linux/.test(ua))   os = 'Linux';
    return os ? `${os} ${browser}` : browser;
}

// ========= イベントバインド =========
document.addEventListener('DOMContentLoaded', () => {
    const regBtn = document.getElementById('passkey-register');
    if (regBtn) regBtn.addEventListener('click', registerPasskey);

    const loginBtn = document.getElementById('passkey-login');
    if (loginBtn) loginBtn.addEventListener('click', () => loginWithPasskey());

    // パスキー名の初期値をブラウザ/デバイス名にセット
    const nameInput = document.getElementById('passkey-name');
    if (nameInput) nameInput.value = getDeviceLabel();

    // ログインページでオートフィルUIを使いたい時だけ有効化
    const isLoginPage = !!document.getElementById('passkey-login');
    if (isLoginPage) enableConditionalUIPrompt();
});
