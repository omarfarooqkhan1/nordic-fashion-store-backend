namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CheckAuth0AccessToken
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key(file_get_contents('https://nordic-leather-products.eu.auth0.com/.well-known/jwks.json'), 'RS256'));

            // optionally verify `aud`, `iss`, `exp`, etc.
            $request->attributes->add(['auth_payload' => (array) $decoded]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token', 'message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
