> _이 기사는 원래 2015년에 [Airpair](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing)에서 출판되었습니다. 모든 크레딧은 Airpair와 원래 이 기사를 작성한 Brian Fenton에게 돌아갑니다. 웹사이트는 더 이상 이용할 수 없으며, 기사는 [Wayback Machine](https://web.archive.org/web/20220204014708/https://www.airpair.com/php/posts/best-practices-for-modern-php-development#5-unit-testing) 내에만 존재합니다. 이 기사는 PHP 커뮤니티의 학습 및 교육 목적으로 사이트에 추가되었습니다._

1 설정 및 구성
-------------------------

### 1.1 최신 상태 유지

가장 먼저 강조할 점은, 야생에서 사용되는 PHP 설치의 놀라울 정도로 적은 수가 최신 상태이거나 최신으로 유지된다는 것입니다. 이는 공유 호스팅 제한, 변경되지 않은 기본 설정, 또는 업그레이드 테스트를 위한 시간/예산 부족 때문일 수 있습니다. 그래서 가장 명확한 최선의 관행은 항상 PHP의 최신 버전(이 기사 작성 시 5.6.x)을 사용하는 것입니다. 게다가, PHP 자체와 사용 중인 확장이나 공급자 라이브러리의 정기적인 업그레이드를 계획하는 것도 중요합니다. 업그레이드는 새로운 언어 기능, 향상된 속도, 낮은 메모리 사용량, 그리고 보안 업데이트를 제공합니다. 업그레이드를 더 자주 수행할수록, 그 과정이 덜 고통스러워집니다.

### 1.2 합리적인 기본 설정

PHP는 _php.ini.development_ 및 _php.ini.production_ 파일을 통해 기본적으로 괜찮은 기본 설정을 제공하지만, 더 나아질 수 있습니다. 예를 들어, 날짜/시간대를 설정하지 않습니다. 이는 배포 관점에서 의미가 있지만, 설정되지 않으면 날짜/시간 관련 함수를 호출할 때마다 E_WARNING 오류가 발생합니다. 아래는 추천 설정입니다:

*   date.timezone - [지원되는 시간대 목록](http://php.net/manual/en/timezones.php)에서 선택하세요.
*   session.save_path - 세션을 파일로 사용하고 다른 저장 처리기를 사용하지 않는 경우, 이것을 _/tmp_ 외부로 설정하세요. _/tmp_를 그대로 두면 공유 호스팅 환경에서 위험할 수 있습니다. _/tmp_는 일반적으로 권한이 넓게 열려 있기 때문에, sticky-bit가 설정되어 있어도 이 디렉터리의 내용을 나열할 수 있는 사람은 모든 활성 세션 ID를 알 수 있습니다.
*   session.cookie_secure - PHP 코드를 HTTPS로 제공하는 경우, 이것을 활성화하세요.
*   session.cookie_httponly - PHP 세션 쿠키가 JavaScript를 통해 접근되지 않도록 설정하세요.
*   더 많은 것... [iniscan](https://github.com/psecio/iniscan) 같은 도구를 사용하여 구성의 일반적인 취약점을 테스트하세요.

### 1.3 확장

사용하지 않을 확장(예: 데이터베이스 드라이버)은 비활성화하거나 활성화하지 않는 것이 좋습니다. 활성화된 확장을 확인하려면, `phpinfo()` 명령을 실행하거나 명령줄에서 실행하세요.

```bash
$ php -i
``` 

정보는 동일하지만, phpinfo()에는 HTML 형식이 추가됩니다. CLI 버전은 grep으로 특정 정보를 찾기 쉽습니다. 예:

```bash
$ php -i | grep error_log
```

이 방법의 주의점: 웹에 노출된 버전과 CLI 버전의 PHP 설정이 다를 수 있습니다.

2 Composer 사용
--------------

현대 PHP를 작성하는 최선의 관행 중 하나는 더 적게 작성하는 것입니다. 프로그래밍을 잘하기 위한 가장 좋은 방법은 실제로 해보는 것이지만, 라우팅, 기본 입력 유효성 검사 라이브러리, 단위 변환, 데이터베이스 추상화 레이어 등 이미 PHP 영역에서 해결된 많은 문제가 있습니다. [Packagist](https://www.packagist.org/)에 가서 둘러보세요. 해결하려는 문제의 상당 부분이 이미 작성되고 테스트된 것을 발견할 수 있습니다.

모든 코드를 스스로 작성하는 유혹이 있을 수 있지만(학습 경험으로 자신의 프레임워크나 라이브러리를 작성하는 데 문제가 없음), Not Invented Here라는 감정을 억제하고 시간을 절약하세요. PIE 원칙을 따르세요 - Proudly Invented Elsewhere. 또한, 자신의 것을 작성하기로 선택했다면, 기존 제품과 크게 다르거나 더 나은 기능을 하지 않는 한 출시하지 마세요.

[Composer](https://www.getcomposer.org/)는 PHP의 패키지 관리자로, Python의 pip, Ruby의 gem, Node의 npm과 유사합니다. JSON 파일을 정의하여 코드의 종속성을 나열하면, 필요한 코드 번들을 다운로드하고 설치하여 요구 사항을 해결합니다.

### 2.1 Composer 설치

이것이 로컬 프로젝트라고 가정하고, 현재 프로젝트 전용 Composer 인스턴스를 설치하세요. 프로젝트 디렉터리로 이동하여 실행하세요:
```bash
$ curl -sS https://getcomposer.org/installer | php
```

이 명령처럼 다운로드를 스크립트 해석기(sh, ruby, php 등)로 직접 전달하는 것은 보안 위험입니다. 설치 코드를 읽고 실행하기 전에 안심하세요.

편의를 위해 (`composer install`을 `php composer.phar install`보다 입력하기를 선호한다면), 전역으로 단일 Composer 복사본을 설치하려면 이 명령을 사용하세요:

```bash
$ mv composer.phar /usr/local/bin/composer
$ chmod +x composer
```

파일 권한에 따라 `sudo`를 사용해야 할 수 있습니다.

### 2.2 Composer 사용

Composer는 두 가지 주요 종속성 카테고리를 관리합니다: "require"와 "require-dev". "require"로 나열된 종속성은 모든 곳에 설치되지만, "require-dev" 종속성은 특별히 요청될 때만 설치됩니다. 보통 이것들은 활발한 개발 중에 사용되는 도구로, [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)와 같은 것입니다. 아래는 [Guzzle](http://docs.guzzlephp.org/en/latest/), 인기 있는 HTTP 라이브러리를 설치하는 예입니다.

```bash
$ php composer.phar require guzzle/guzzle
```

개발 목적으로만 도구를 설치하려면, `--dev` 플래그를 추가하세요:

```bash
$ php composer.phar require --dev 'sebastian/phpcpd'
```

이것은 [PHP Copy-Paste Detector](https://github.com/sebastianbergmann/phpcpd)를 개발 전용 종속성으로 설치합니다.

### 2.3 Install vs update

처음 `composer install`을 실행하면, _composer.json_ 파일에 기반하여 필요한 라이브러리와 그 종속성을 설치합니다. 완료되면, composer는 _composer.lock_ 파일을 생성합니다. 이 파일은 composer가 찾은 종속성과 그 정확한 버전, 해시를 포함합니다. 이후 `composer install`을 실행할 때, 이 잠금 파일을 확인하고 해당 정확한 버전을 설치합니다.

`composer update`은 다릅니다. _composer.lock_ 파일(존재하는 경우)을 무시하고, _composer.json_의 제약 조건을 만족하는 가장 최신 버전의 각 종속성을 찾습니다. 완료되면 새 _composer.lock_ 파일을 작성합니다.

### 2.4 Autoloading

composer install과 composer update은 [autoloader](https://getcomposer.org/doc/04-schema.md#autoload)를 생성하여, 방금 설치한 라이브러리를 사용하기 위한 필요한 파일의 위치를 PHP에 알려줍니다. 사용하려면, 이 줄을 추가하세요(보통 모든 요청에서 실행되는 부트스트랩 파일에):

```php
require 'vendor/autoload.php';
```

3 좋은 디자인 원칙 따르기
-------------------------------

### 3.1 SOLID

SOLID는 좋은 객체 지향 소프트웨어 디자인의 다섯 가지 핵심 원칙을 상기시키는 기억 장치입니다.

#### 3.1.1 S - 단일 책임 원칙

이 원칙은 클래스가 하나의 책임만 가져야 하며, 다른 말로 하면 변경될 단 하나의 이유만 가져야 한다고 말합니다. 이는 작은 도구를 많이 사용하고 한 가지를 잘 수행하는 Unix 철학과 잘 맞습니다. 하나의 일만 하는 클래스는 테스트하고 디버그하기 훨씬 쉽고, 놀라움을 주지 않습니다. Validator 클래스의 메서드 호출이 DB 레코드를 업데이트하는 것을 원하지 않습니다. 아래는 [ActiveRecord pattern](http://en.wikipedia.org/wiki/Active_record_pattern)에 기반한 애플리케이션에서 흔히 볼 수 있는 SRP 위반 예입니다.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
    public function save() {}
}
```
    

이것은 기본적인 [entity](http://lostechies.com/jimmybogard/2008/05/21/entities-value-objects-aggregates-and-roots/) 모델입니다. 하지만 이 중 하나의 것이 여기에 속하지 않습니다. 엔티티 모델의 유일한 책임은 그것이 나타내는 엔티티와 관련된 행동일 뿐, 자신을 영속화하는 책임은 가져서는 안 됩니다.

```php
class Person extends Model
{
    public $name;
    public $birthDate;
    protected $preferences;
    public function getPreferences() {}
}
class DataStore
{
    public function save(Model $model) {}
}
```

이것이 더 낫습니다. Person 모델은 하나의 일만 하고, 저장 행동은 영속성 객체로 이동되었습니다. 또한 Model만 타입 힌팅한 점에 주목하세요. L과 D 부분에서 다시 다룰 것입니다.

#### 3.1.2 O - 개방 폐쇄 원칙

이 원칙을 요약하는 훌륭한 테스트가 있습니다: 구현할 기능, 아마도 최근에 작업한 또는 작업 중인 기능을 생각하세요. 기존 코드베이스에서 이 기능을 오직 새 클래스를 추가하는 것만으로, 기존 클래스를 변경하지 않고 구현할 수 있나요? 구성과 배선 코드는 약간 예외지만, 대부분의 시스템에서 이것은 놀라울 정도로 어렵습니다. 다형성 디스패치를 많이 의존해야 하며, 대부분의 코드베이스는 그렇게 설정되어 있지 않습니다. 관심이 있으시면, [polymorphism and writing code without Ifs](https://www.youtube.com/watch?v=4F72VULWFvc)에 대한 좋은 Google 토크가 YouTube에 있습니다. 보너스로, 이 토크는 [Miško Hevery](http://misko.hevery.com/), [AngularJs](https://angularjs.org/)의 제작자로 알려진 사람이 합니다.

#### 3.1.3 L - Liskov 대체 원칙

이 원칙은 [Barbara Liskov](http://en.wikipedia.org/wiki/Barbara_Liskov)의 이름을 따서 명명되었으며, 아래와 같습니다:

> "프로그램의 객체는 하위 타입의 인스턴스로 대체할 수 있어야 하며, 프로그램의 정확성을 변경하지 않아야 합니다."

이것은 잘 들리지만, 예를 통해 더 명확히 설명됩니다.

```php
abstract class Shape
{
    public function getHeight();
    public function setHeight($height);
    public function getLength();
    public function setLength($length);
}
```   

이것은 기본적인 네 변 모양을 나타냅니다. 특별한 것은 없습니다.

```php
class Square extends Shape
{
    protected $size;
    public function getHeight() {
        return $this->size;
    }
    public function setHeight($height) {
        $this->size = $height;
    }
    public function getLength() {
        return $this->size;
    }
    public function setLength($length) {
        $this->size = $length;
    }
}
```

첫 번째 모양인 Square입니다. 간단한 모양입니다. 생성자가 차원을 설정한다고 가정할 수 있지만, 이 구현에서 길이와 높이가 항상 같다는 것을 볼 수 있습니다. Square는 그저 그렇습니다.

```php
class Rectangle extends Shape
{
    protected $height;
    protected $length;
    public function getHeight() {
        return $this->height;
    }
    public function setHeight($height) {
        $this->height = $height;
    }
    public function getLength() {
        return $this->length;
    }
    public function setLength($length) {
        $this->length = $length;
    }
}
```

여기에는 다른 모양이 있습니다. 여전히 같은 메서드 서명이지만, 네 변 모양이지만, 서로 대체하려고 시작하면 어떨까요? 이제 Shape의 높이를 변경하면, 모양의 길이가 일치한다고 가정할 수 없게 됩니다. Square 모양을 제공할 때 사용자와 맺은 계약을 위반했습니다.

이것은 LSP 위반의 교과서적인 예이며, 타입 시스템을 최적으로 사용하기 위해 이 유형의 원칙이 필요합니다. [duck typing](http://en.wikipedia.org/wiki/Duck_typing)조차도 기본 행동이 다르다는 것을 알려주지 않으며, 그것이 깨지는 것을 보지 않고는 알 수 없기 때문에, 처음부터 다르지 않도록 하는 것이 가장 좋습니다.

#### 3.1.3 I - 인터페이스 분리 원칙

이 원칙은 하나의 큰 인터페이스보다 많은 작은, 세밀한 인터페이스를 선호합니다. 인터페이스는 "이 클래스 중 하나"가 아닌 행동에 기반해야 합니다. PHP에 포함된 인터페이스를 생각하세요. Traversable, Countable, Serializable 등입니다. 그것들은 객체가 가진 능력을 광고할 뿐, 상속된 것이 아닙니다. 그래서 인터페이스를 작게 유지하세요. 30개의 메서드가 있는 인터페이스를 원하지 않습니다. 3개가 더 좋은 목표입니다.

#### 3.1.4 D - 의존성 역전 원칙

이것은 [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection)에 대해 다른 곳에서 들어보셨을 수 있지만, 의존성 역전과 의존성 주입은 완전히 같은 것이 아닙니다. 의존성 역전은 시스템의 세부 사항이 아닌 추상화에 의존해야 한다고 말합니다. 일상적으로 이것이 무엇을 의미하나요?

> 코드 전체에 mysqli_query()를 직접 사용하지 말고, DataStore->query() 같은 것을 사용하세요.

이 원칙의 핵심은 추상화에 관한 것입니다. "데이터베이스 어댑터 사용"이라고 말하는 것처럼, mysqli_query 같은 직접 호출에 의존하지 마세요. mysqli_query를 절반의 클래스에서 직접 사용하면, 모든 것을 데이터베이스에 직접 연결합니다. MySQL에 대해 긍정적이거나 부정적인 것이 아니지만, 이 유형의 낮은 수준 세부 사항은 한 곳에 숨겨져야 하며, 그 기능은 일반적인 래퍼를 통해 노출되어야 합니다.

이것이 다소 진부한 예일 수 있지만, 제품을 프로덕션에 배포한 후 데이터베이스 엔진을 완전히 변경하는 경우는 매우 드물기 때문에 선택했습니다. 또한, 고정된 데이터베이스를 사용하더라도, 그 추상 래퍼 객체는 버그 수정, 행동 변경, 또는 원하는 기능을 구현할 수 있게 합니다. 또한, 낮은 수준 호출에서는 불가능한 단위 테스트를 가능하게 합니다.

4 객체 캘리세니즘
---------------------

이 원칙에 대한 완전한 다이빙은 아니지만, 처음 두 가지는 쉽게 기억할 수 있고, 좋은 가치를 제공하며, 거의 모든 코드베이스에 즉시 적용할 수 있습니다.

### 4.1 메서드당 들여쓰기 레벨 하나만

이것은 메서드를 더 작은 청크로 분해하여, 더 명확하고 자문서화된 코드를 남기는 데 도움이 됩니다. 들여쓰기 레벨이 많을수록, 메서드가 더 많은 일을 하고, 작업 중에 추적해야 할 상태가 많아집니다.

바로 사람들은 이 점에 반대할 수 있지만, 이것은 가이드라인/휴리스틱일 뿐, 엄격한 규칙이 아닙니다. PHP_CodeSniffer 규칙을 이로 강제할 생각은 없습니다(비록 [사람들이 했지만](https://github.com/object-calisthenics/phpcs-calisthenics-rules)).

빠른 샘플을 실행해 보겠습니다:

```php
public function transformToCsv($data)
{
    $csvLines = array();
    $csvLines[] = implode(',', array_keys($data[0]));
    foreach ($data as $row) {
        if (!$row) {
            continue;
        }
        $csvLines[] = implode(',', $row);
    }
    return $csvLines;
}
```

이 코드는 나쁘지 않지만(기술적으로 올바르고, 테스트 가능 등), 더 명확하게 만들 수 있습니다. 여기서 중첩 레벨을 줄이기 위해 어떻게 할까요?

먼저 foreach 루프의 내용을 간단히 해야 합니다(또는 완전히 제거).

```php
if (!$row) {
    continue;
}
```   

이 부분은 쉽습니다. 비어 있는 행을 무시하는 것뿐입니다. 루프에 도달하기 전에 내장 PHP 함수를 사용하여 이 과정을 단축할 수 있습니다.

```php
$data = array_filter($data);
foreach ($data as $row) {
    $csvLines[] = implode(',', $row);
}
```

이제 단일 중첩 레벨이 있습니다. 하지만 이것을 보면, 배열의 각 항목에 함수를 적용하는 것뿐입니다. foreach 루프조차 필요하지 않습니다.

```php
$data = array_filter($data);
$csvLines = array_map(function($row) {
    return implode(',', $row);
}, $data);
```

이제 중첩이 전혀 없고, 코드는 더 빠를 수 있습니다. 루핑을 네이티브 C 함수로 하기 때문입니다. 쉼표를 `implode`에 전달하기 위해 약간의 트릭을 사용해야 하지만, 이전 단계에서 멈추는 것이 더 이해하기 쉽다고 주장할 수 있습니다.

### 4.2 `else` 사용 피하기

이것은 두 가지 주요 아이디어를 다룹니다. 첫 번째는 메서드에서 여러 반환 문입니다. 메서드의 결과를 결정할 충분한 정보가 있으면, 그 결정 후 반환하세요. 두 번째는 [Guard Clauses](http://c2.com/cgi/wiki?GuardClause)로 알려진 아이디어입니다. 이것은 기본적으로 메서드 상단 근처에서 검증 검사와 조기 반환을 결합합니다. 보여드리겠습니다.

```php
public function addThreeInts($first, $second, $third) {
    if (is_int($first)) {
        if (is_int($second)) {
            if (is_int($third)) {
                $sum = $first + $second + $third;
            } else {
                return null;
            }
        } else {
            return null;
        }
    } else {
        return null;
    }
    return $sum;
}
```

이것은 간단합니다. 3개의 정수를 더하고 결과를 반환하거나, 매개변수가 정수가 아니면 `null`을 반환합니다. AND 연산자로 모든 검사를 한 줄에 결합할 수 있지만, 중첩 if/else 구조가 코드를 따라가기 더 어렵다는 것을 볼 수 있습니다. 이제 이 예를 보세요.

```php
public function addThreeInts($first, $second, $third) {
    if (!is_int($first)) {
        return null;
    }
    if (!is_int($second)) {
        return null;
    }
    if (!is_int($third)) {
        return null;
    }
    return $first + $second + $third;
}
```   

이 예는 훨씬 따라가기 쉽습니다. 여기서 guard clauses를 사용하여 매개변수에 대한 초기 주장을 검증하고, 통과하지 않으면 메서드를 즉시 종료합니다. 또한 합계를 추적하는 중간 변수를 더 이상 가지지 않습니다. 이미 행복한 경로에 있음을 확인했으므로, 할 일을 하세요. 모든 검사를 하나의 `if`로 할 수 있지만, 원칙은 명확합니다.

5 단위 테스트
--------------

단위 테스트는 코드의 행동을 확인하는 작은 테스트를 작성하는 관행입니다. 거의 항상 코드와 같은 언어(이 경우 PHP)로 작성되며, 언제든지 실행할 수 있을 만큼 빠릅니다. 코드 개선을 위한 매우 유용한 도구입니다. 코드가 예상대로 작동하는지 확인하는 명백한 이점 외에도, 단위 테스트는 디자인 피드백을 제공할 수 있습니다. 테스트하기 어려운 코드 조각은 종종 디자인 문제를 드러냅니다. 또한 회귀에 대한 안전망을 제공하여, 더 자주 리팩터링하고 코드를 더 깨끗한 디자인으로 진화시킬 수 있습니다.

### 5.1 도구

PHP에는 여러 단위 테스트 도구가 있지만, 단연코 가장 일반적인 것은 [PHPUnit](https://phpunit.de/)입니다. [PHAR](http://php.net/manual/en/intro.phar.php) 파일을 [직접](https://phar.phpunit.de/phpunit.phar) 다운로드하거나, composer로 설치할 수 있습니다. 다른 모든 것에 composer를 사용하므로, 그 방법을 보여드리겠습니다. 또한, PHPUnit는 프로덕션에 배포되지 않을 가능성이 크므로, dev 종속성으로 설치하세요:

```bash
composer require --dev phpunit/phpunit
```

### 5.2 테스트는 사양

코드의 단위 테스트에서 가장 중요한 역할은 코드가 해야 할 일을 실행 가능한 사양으로 제공하는 것입니다. 테스트 코드가 잘못되었거나 코드에 버그가 있더라도, 시스템이 해야 할 일에 대한 지식은 귀중합니다.

### 5.3 먼저 테스트 작성

코드 전에 테스트를 작성한 경우와 코드 후에 작성한 경우를 본 적이 있다면, 그 차이가 뚜렷합니다. "후" 테스트는 클래스의 구현 세부 사항에 훨씬 더 관심이 있고, 좋은 라인 커버리지를 확보하는 데 초점을 맞춥니다. 반면, "전" 테스트는 원하는 외부 행동을 확인하는 데 더 관심이 있습니다. 이것이 단위 테스트에서 실제로 원하는 것입니다. 클래스 내부가 변경되면 구현 중심 테스트는 리팩터링을 더 어렵게 만듭니다.

### 5.4 좋은 단위 테스트의 특징

좋은 단위 테스트는 다음과 같은 특징을 공유합니다:

*   빠름 - 밀리초 단위로 실행되어야 합니다.
*   네트워크 접근 없음 - 무선 끄거나 unplug 해도 모든 테스트가 통과해야 합니다.
*   파일 시스템 접근 제한 - 속도와 다른 환경에 배포할 때의 유연성을 높입니다.
*   데이터베이스 접근 없음 - 비용이 큰 설정과 해체 활동을 피합니다.
*   한 번에 하나의 것만 테스트 - 단위 테스트는 실패할 단 하나의 이유만 가져야 합니다.
*   잘 명명됨 - 5.2를 참조하세요.
*   대부분 가짜 객체 - 단위 테스트에서 "실제" 객체는 테스트 중인 객체와 간단한 값 객체일 뿐입니다. 나머지는 [test double](https://phpunit.de/manual/current/en/test-doubles.html) 형태여야 합니다.

이 중 일부를 위반할 이유가 있지만, 일반적인 지침으로 유용합니다.

### 5.5 테스트가 고통스러울 때

> 단위 테스트는 나쁜 디자인의 고통을 앞당겨 느끼게 합니다 - Michael Feathers

단위 테스트를 작성할 때, 클래스를 실제로 사용해야 합니다. 테스트를 끝에 작성하거나, 더 나쁘게는 QA나 누구에게 코드 던지면, 클래스의 실제 행동에 대한 피드백을 얻지 못합니다. 테스트를 작성 중이고 클래스가 사용하기 어려우면, 작성 중에 알 수 있습니다. 이것은 거의 가장 저렴한 수정 시기입니다.

클래스가 테스트하기 어렵다면, 디자인 결함입니다. 다른 결함이 다르게 나타나지만, mocking을 많이 해야 한다면 클래스가 너무 많은 종속성을 가지거나 메서드가 너무 많은 일을 할 수 있습니다. 각 테스트에 많은 설정을 해야 한다면, 메서드가 너무 많은 일을 할 가능성이 큽니다. 행동을 실행하기 위해 복잡한 테스트 시나리오를 작성해야 한다면, 클래스의 메서드가 너무 많은 일을 할 수 있습니다. 사적인 메서드와 상태를 테스트하기 위해 파고들어야 한다면, 다른 클래스가 나오려고 할 수 있습니다. 단위 테스트는 "iceberg classes"를 드러내는 데 매우 좋습니다. 클래스의 80%가 보호되거나 사적인 코드에 숨겨져 있습니다. 이전에 가능한 한 많이 보호하는 것을 좋아했지만, 이제 각 클래스가 너무 많은 책임을 지고 있다는 것을 깨달았고, 진짜 해결책은 클래스를 더 작은 조각으로 나누는 것입니다.

> **Brian Fenton 작성** - Brian Fenton은 중서부와 Bay Area에서 8년 동안 PHP 개발자였으며, 현재 Thismoment에 있습니다. 그는 코드 장인 정신과 디자인 원칙에 초점을 맞춥니다. 블로그 www.brianfenton.us, Twitter @brianfenton. 아이를 키우는 데 바쁘지 않을 때는 음식, 맥주, 게임, 학습을 즐깁니다.