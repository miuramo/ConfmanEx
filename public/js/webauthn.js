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
        //    Laragear既定: GET /webauthn/register/options もしくは POST
        const opts = await get('/webauthn/register/options');

        // 2) オプションのBase64URL→ArrayBuffer整形
        const publicKey = normalizeCreationOptions(opts);

        // 3) 生体認証/セキュリティキーで鍵ペア生成
        const cred = await navigator.credentials.create({ publicKey });
        if (!cred) throw new Error('credential was null');

        // 4) サーバへ登録結果(Attestation)を送信
        const payload = attestationToJSON(cred);
        await post('/webauthn/register', payload);

        alert('パスキーを登録しました！');
        // 必要ならページ更新や鍵一覧再取得など

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
        const opts = await post('/webauthn/login/options', {});
        const publicKey = normalizeRequestOptions(opts);

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
        const payload = assertionToJSON(cred);
        await post('/webauthn/login', payload);

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

// ========= お行儀よくエラーを整形 =========
function handleWebAuthnError(err, fallback) {
    // 代表的ブラウザエラーをユーザー向けに言い換え
    const msg = (err?.name) ? ({
        NotAllowedError: '操作がキャンセルされました（タイムアウト/キャンセル）',
        InvalidStateError: 'そのパスキーは既に登録済みか、使えない状態です',
        SecurityError: 'オリジン/HTTPS要件を満たしていません',
        ConstraintError: 'プラットフォーム要件を満たせませんでした',
        UnknownError: '認証器で予期しないエラーが発生しました'
    }[err.name] || `${fallback}: ${err.message || err}`) : `${fallback}: ${err?.message || err}`;

    console.error(err);
    alert(msg);
}

// ========= イベントバインド =========
document.addEventListener('DOMContentLoaded', () => {
    const regBtn = document.getElementById('passkey-register');
    if (regBtn) regBtn.addEventListener('click', registerPasskey);

    const loginBtn = document.getElementById('passkey-login');
    if (loginBtn) loginBtn.addEventListener('click', () => loginWithPasskey());

    // ログインページでオートフィルUIを使いたい時だけ有効化
    const isLoginPage = !!document.getElementById('passkey-login');
    if (isLoginPage) enableConditionalUIPrompt();
});
