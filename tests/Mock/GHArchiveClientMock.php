<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use App\Client\GHArchiveClient;

class GHArchiveClientMock extends GHArchiveClient
{
    public function downloadEvents(string $date, string $hour, array $options = []): string
    {
        $gzipFile = "$date-$hour.json.gz";

        $content = '{"id":"42465860270","type":"PushEvent","actor":{"id":29113998,"login":"lindseysimple","display_login":"lindseysimple","gravatar_id":"","url":"https://api.github.com/users/lindseysimple","avatar_url":"https://avatars.githubusercontent.com/u/29113998?"},"repo":{"id":253402714,"name":"lindseysimple/edgex-go","url":"https://api.github.com/repos/lindseysimple/edgex-go"},"payload":{"repository_id":253402714,"push_id":20514943651,"size":1,"distinct_size":1,"ref":"refs/heads/issue-4694","head":"20fb8c8a7ac2dfcaf368b06d468eb2d1bd4c98c5","before":"5a6d4793b8347237aa1cb70ff4ecb2deefa1335b","commits":[{"sha":"20fb8c8a7ac2dfcaf368b06d468eb2d1bd4c98c5","author":{"email":"beckysocute@gmail.com","name":"Lindsey Cheng"},"message":"fix: Rename forceAddDevice func to update Device\n\n- Rename forceAddDevice func to update Device\n- Publish update device system event for force add\n\nSigned-off-by: Lindsey Cheng <beckysocute@gmail.com>","distinct":true,"url":"https://api.github.com/repos/lindseysimple/edgex-go/commits/20fb8c8a7ac2dfcaf368b06d468eb2d1bd4c98c5"}]},"public":true,"created_at":"2024-10-02T09:00:00Z"}'
            . PHP_EOL
            . '{"id":"42465860274","type":"PushEvent","actor":{"id":182098737,"login":"TVhhabjRJC","display_login":"TVhhabjRJC","gravatar_id":"","url":"https://api.github.com/users/TVhhabjRJC","avatar_url":"https://avatars.githubusercontent.com/u/182098737?"},"repo":{"id":861751806,"name":"TVhhabjRJC/crypto-address-monitoring","url":"https://api.github.com/repos/TVhhabjRJC/crypto-address-monitoring"},"payload":{"repository_id":861751806,"push_id":20514943652,"size":1,"distinct_size":1,"ref":"refs/heads/main","head":"6323f4074587bb26e1d7f3d3a8c952ca1ce03156","before":"100fe6b488babbce3934c7c012261e1e9a265d26","commits":[{"sha":"6323f4074587bb26e1d7f3d3a8c952ca1ce03156","author":{"email":"vugamobemugo1994@outlook.com","name":"TVhhabjRJC"},"message":"Minor change to README","distinct":true,"url":"https://api.github.com/repos/TVhhabjRJC/crypto-address-monitoring/commits/6323f4074587bb26e1d7f3d3a8c952ca1ce03156"}]},"public":true,"created_at":"2024-10-02T09:00:00Z"}'
            . PHP_EOL
            . '{"id":"42465860274","type":"DiscardedEvent","actor":{"id":182098737,"login":"TVhhabjRJC","display_login":"TVhhabjRJC","gravatar_id":"","url":"https://api.github.com/users/TVhhabjRJC","avatar_url":"https://avatars.githubusercontent.com/u/182098737?"},"repo":{"id":861751806,"name":"TVhhabjRJC/crypto-address-monitoring","url":"https://api.github.com/repos/TVhhabjRJC/crypto-address-monitoring"},"payload":{"repository_id":861751806,"push_id":20514943652,"size":1,"distinct_size":1,"ref":"refs/heads/main","head":"6323f4074587bb26e1d7f3d3a8c952ca1ce03156","before":"100fe6b488babbce3934c7c012261e1e9a265d26","commits":[{"sha":"6323f4074587bb26e1d7f3d3a8c952ca1ce03156","author":{"email":"vugamobemugo1994@outlook.com","name":"TVhhabjRJC"},"message":"Minor change to README","distinct":true,"url":"https://api.github.com/repos/TVhhabjRJC/crypto-address-monitoring/commits/6323f4074587bb26e1d7f3d3a8c952ca1ce03156"}]},"public":true,"created_at":"2024-10-02T09:00:00Z"}'
            . PHP_EOL
        ;

        $gz = gzopen($gzipFile, 'w');

        if ($gz === false) {
            throw new \Exception('Impossible d\'ouvrir le fichier Gzip.');
        }

        gzwrite($gz, $content);
        gzclose($gz);

        return $gzipFile;
    }
}
