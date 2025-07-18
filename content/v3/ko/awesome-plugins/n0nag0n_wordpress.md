# 워드프레스 통합: n0nag0n/wordpress-integration-for-flight-framework

워드프레스 사이트에서 Flight PHP를 사용하고 싶으신가요? 이 플러그인을 사용하면 간단하게 가능합니다! `n0nag0n/wordpress-integration-for-flight-framework`을 통해 워드프레스 설치와 함께 완전한 Flight 앱을 실행할 수 있습니다. 이는 맞춤형 API, 마이크로서비스, 또는 완전한 기능을 갖춘 앱을 구축하는 데 이상적입니다.

---

## 이 플러그인이 하는 일?

- **Flight PHP를 워드프레스와 원활하게 통합**
- URL 패턴에 따라 요청을 Flight 또는 워드프레스로 라우팅
- 컨트롤러, 모델, 뷰(MVC)를 사용하여 코드 구성
- 권장되는 Flight 폴더 구조를 쉽게 설정
- 워드프레스의 데이터베이스 연결 또는 자체 연결 사용
- Flight와 워드프레스의 상호작용을 미세 조정
- 간단한 관리자 인터페이스로 구성

## 설치

1. `flight-integration` 폴더를 `/wp-content/plugins/` 디렉터리에 업로드합니다.
2. 워드프레스 관리자(플러그인 메뉴)에서 플러그인을 활성화합니다.
3. **설정 > Flight Framework**로 이동하여 플러그인을 구성합니다.
4. Flight 설치의 벤더 경로를 설정합니다(또는 Composer를 사용하여 Flight를 설치).
5. 앱 폴더 경로를 구성하고 폴더 구조를 생성합니다(플러그인이 이를 도와줍니다!).
6. Flight 애플리케이션 구축을 시작합니다!

## 사용 예시

### 기본 라우트 예시
앱의 `app/config/routes.php` 파일에서:

```php
Flight::route('GET /api/hello', function() {
    Flight::json(['message' => 'Hello World!']);
});
```

### 컨트롤러 예시

`app/controllers/ApiController.php`에 컨트롤러를 생성합니다:

```php
namespace app\controllers;

use Flight;

class ApiController {
    public function getUsers() {
        // 워드프레스 함수를 Flight 내부에서 사용할 수 있습니다!
        $users = get_users();
        $result = [];
        foreach($users as $user) {
            $result[] = [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email
            ];
        }
        Flight::json($result);
    }
}
```

그런 다음, `routes.php`에서:

```php
Flight::route('GET /api/users', [app\controllers\ApiController::class, 'getUsers']);
```

## FAQ

**Q: 이 플러그인을 사용하려면 Flight를 알아야 하나요?**  
A: 네, 이는 워드프레스 내에서 Flight를 사용하고 싶은 개발자를 위한 것입니다. Flight의 라우팅과 요청 처리에 대한 기본 지식이 추천됩니다.

**Q: 이 플러그인이 워드프레스 사이트 속도를 느리게 할까요?**  
A: 아니요! 이 플러그인은 Flight 라우트와 일치하는 요청만 처리합니다. 다른 요청은 평소처럼 워드프레스로 이동합니다.

**Q: Flight 앱에서 워드프레스 함수를 사용할 수 있나요?**  
A: 물론입니다! Flight 라우트와 컨트롤러 내에서 모든 워드프레스 함수, 후크, 글로벌 변수에 완전히 접근할 수 있습니다.

**Q: 사용자 정의 라우트를 어떻게 생성하나요?**  
A: 앱 폴더의 `config/routes.php` 파일에서 라우트를 정의합니다. 폴더 구조 생성기가 만든 샘플 파일을 참조하여 예시를 보세요.

## 변경 로그

**1.0.0**  
초기 릴리스.

---

추가 정보는 [GitHub repo](https://github.com/n0nag0n/wordpress-integration-for-flight-framework)를 확인하세요.