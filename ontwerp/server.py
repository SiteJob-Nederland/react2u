"""Alleen de ontwerpbestanden lokaal tonen; geen directorylijst of broncode serveren."""
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
import re
from urllib.parse import unquote, urlsplit

ROOT = Path(__file__).resolve().parent
ALLOWED = {"index.html", "werkgevers.html", "werknemers.html", "contact.html", "blog.html", "kennisbank.html", "ontwerp.css", "ontwerp.js",
           "over-react2u.html", "verzuimprotocol.html", "verzuimbegeleiding-wvp.html", "verzuimbegeleiding-erd-zw.html",
           "preventie-en-vitaliteit.html", "begeleiding-en-coaching.html", "trainingen-en-workshops.html", "risicomanagement.html",
           "assets/logo.png", "assets/favicon.png", "assets/display-var-latin.woff2", "assets/body-var-latin.woff2"}


class PreviewHandler(SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=str(ROOT), **kwargs)

    def send_head(self):
        requested = unquote(urlsplit(self.path).path).lstrip("/") or "index.html"
        if requested not in ALLOWED and not re.fullmatch(r"(?:assets/quality/[a-f0-9]{16}-\d+\.webp|ontwerp\.quality\.[a-f0-9]{12}\.(?:css|js))", requested):
            self.send_error(404)
            return None
        return super().send_head()

    def end_headers(self):
        self.send_header("X-Robots-Tag", "noindex, nofollow")
        self.send_header("Cache-Control", "no-store")
        self.send_header("X-Content-Type-Options", "nosniff")
        self.send_header("Content-Security-Policy", "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self'; connect-src 'none'; object-src 'none'; base-uri 'none'; frame-ancestors 'none'; form-action 'none'")
        super().end_headers()


if __name__ == "__main__":
    print("React2u ontwerp: http://127.0.0.1:8133", flush=True)
    ThreadingHTTPServer(("127.0.0.1", 8133), PreviewHandler).serve_forever()
