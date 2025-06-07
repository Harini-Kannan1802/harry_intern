<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "newdbs");
$user_id = $_SESSION['user_id'];

$user = $conn->query("SELECT * FROM studentss WHERE id = $user_id")->fetch_assoc();
$city = $user['city'];
$_SESSION['user_city'] = $city;

$apiKey = '27777a67ae5ce2c91fcb9d8b61cc0bd5';

function fetchWeatherData($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$currentWeatherUrl = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$apiKey}&units=metric";
$forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?q={$city}&appid={$apiKey}&units=metric";

$currentWeatherData = fetchWeatherData($currentWeatherUrl);
$forecastData = fetchWeatherData($forecastUrl);
$nextSixHoursForecast = array_slice($forecastData['list'], 0, 6);

$posts = $conn->query("SELECT posts.*, studentss.name, studentss.profile_pic FROM posts JOIN studentss ON posts.user_id = studentss.id ORDER BY posts.created_at DESC");

// Fetch news from GNews API
$gnewsApiKey = 'c47979b8b2e8c182ea65e93f3a35ac1f'; // Replace this with your actual API key
$newsUrl = "https://gnews.io/api/v4/top-headlines?token={$gnewsApiKey}&lang=en&country=in&max=50";
$newsData = fetchWeatherData($newsUrl);
$newsArticles = $newsData['articles'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: rgb(7, 167, 167);
      font-family: 'Poppins', 'Segoe UI', sans-serif;
    }
    .profile-pic {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
    }
    .emoji {
      font-size: 2rem;
      color: black;
    }
    .forecast-item {
      display: inline-block;
      width: 150px;
      background: rgb(245, 245, 220);
      padding: 10px;
      margin: 5px;
      border-radius: 10px;
    }
    .forecast-item:hover {
      transform: scale(1.05);
      background:rgb(0, 225, 255);
    }
    .fixed-media {
      width: 100%;
      max-width: 600px;
      aspect-ratio: 1 / 1;
      object-fit: cover;
      border-radius: 15px;
      margin: 0 auto;
      display: block;
      border: 3px solid #ccc;
    }
    .card.post {
      max-width: 600px;
      margin: 20px auto;
      background: rgb(245, 245, 220);
      color: black;
    }
    .upload-card {
      background: #fff9e6;
      border-left: 5px solid #ffc107;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .upload-card h5 { font-weight: 600; color: #333; }
    .upload-card .form-control, .upload-card .btn { border-radius: 12px; }

    .weather-card, .news-card {
      background: #e6f7ff;
      border-left: 5px solid #0dcaf0;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      color: black;
    }
    .news-card h5 { font-weight: 600; color: #0a3d62; }
    .news-card ul { padding-left: 1.2em; }
    .news-card li { margin-bottom: 10px; }

    .like-count, .card-body p, .card-footer p, .card-footer strong, .card-header strong, .forecast-item p, .weather-card p {
      color: black;
    }
    .forecast-section { color: black; margin-top: 20px; }

    @media (min-width: 992px) {
      .row .left-column { width: 65%; float: left; }
      .row .right-column { width: 35%; float: right; }
    }
  </style>
</head>
<body>
<div class="container my-4">
  <div class="text-center mb-4">
    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
    <?php if (!empty($user['profile_pic'])): ?>
      <img src="<?php echo $user['profile_pic']; ?>" class="profile-pic mt-2" alt="Profile Picture">
    <?php endif; ?>
  </div>

  <div class="row">
    <div class="left-column">
      <!-- Upload Post -->
      <div class="card upload-card mb-4">
        <h5>Upload New Post</h5>
        <form action="upload.php" method="POST" enctype="multipart/form-data" class="row g-2">
          <div class="col-md-4">
            <input type="file" class="form-control" name="post_file" accept="image/*,video/*" required>
          </div>
          <div class="col-md-6">
            <input type="text" class="form-control" name="description" placeholder="Description" required>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Post</button>
          </div>
        </form>
      </div>

      <!-- Posts -->
      <h4 class="my-4">Recent Posts</h4>
      <?php while ($post = $posts->fetch_assoc()): ?>
        <div class="card post">
          <div class="card-header d-flex align-items-center">
            <?php if (!empty($post['profile_pic'])): ?>
              <img src="<?php echo $post['profile_pic']; ?>" class="rounded-circle me-2" width="40" height="40">
            <?php endif; ?>
            <strong><?php echo htmlspecialchars($post['name']); ?></strong>
          </div>
          <div class="card-body">
            <?php if ($post['file_type'] == 'image'): ?>
              <img src="uploads/<?php echo $post['file_name']; ?>" class="img-fluid rounded mb-3 fixed-media">
            <?php elseif ($post['file_type'] == 'video'): ?>
              <video class="img-fluid mb-3 fixed-media" controls>
                <source src="uploads/<?php echo $post['file_name']; ?>" type="video/<?php echo pathinfo($post['file_name'], PATHINFO_EXTENSION); ?>">
              </video>
            <?php endif; ?>

            <p><?php echo htmlspecialchars($post['description']); ?></p>

            <?php
              $liked = $conn->query("SELECT * FROM likes WHERE post_id = {$post['id']} AND user_id = $user_id")->num_rows;
              $likeCount = $conn->query("SELECT COUNT(*) FROM likes WHERE post_id = {$post['id']}")->fetch_row()[0];
            ?>
            <button type="button" class="btn btn-outline-danger like-btn" data-post-id="<?php echo $post['id']; ?>">
              <i class="bi bi-heart<?php echo $liked ? '-fill' : ''; ?>"></i> Like
            </button>
            <p class="like-count mt-1"><?php echo $likeCount; ?> likes</p>
          </div>

          <!-- Comments -->
          <div class="card-footer">
            <div class="comment-section" id="comments-<?php echo $post['id']; ?>">
              <?php
              $comments = $conn->query("SELECT c.comment_text, s.name FROM comments c JOIN studentss s ON c.user_id = s.id WHERE c.post_id = {$post['id']} ORDER BY c.created_at DESC");
              while ($c = $comments->fetch_assoc()):
              ?>
                <p><strong><?php echo htmlspecialchars($c['name']); ?>:</strong> <?php echo htmlspecialchars($c['comment_text']); ?></p>
              <?php endwhile; ?>
            </div>

            <form class="comment-form mt-2" data-post-id="<?php echo $post['id']; ?>">
              <div class="input-group">
                <input type="text" name="comment_text" class="form-control" placeholder="Write a comment" required>
                <button class="btn btn-secondary" type="submit">Comment</button>
              </div>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <!-- Weather and News -->
    <div class="right-column">
      <!-- Weather -->
      <div class="card weather-card mb-4">
        <h5 class="mb-3">Current Weather in <?php echo htmlspecialchars($city); ?></h5>
        <p class="emoji">
          <?php
          $weatherMain = $currentWeatherData['weather'][0]['main'];
          $weatherEmojis = [
              'Clear' => '☀️', 'Clouds' => '☁️', 'Rain' => '🌧️',
              'Drizzle' => '🌦️', 'Thunderstorm' => '⛈️', 'Snow' => '❄️',
              'Mist' => '🌫️', 'Smoke' => '💨', 'Haze' => '🌫️',
              'Dust' => '🌪️', 'Fog' => '🌫️', 'Sand' => '🏜️',
              'Ash' => '🌋', 'Squall' => '🌬️', 'Tornado' => '🌪️'
          ];
          echo $weatherEmojis[$weatherMain] ?? '🌈';
          ?>
        </p>
        <p><strong><?php echo $currentWeatherData['weather'][0]['description']; ?></strong></p>
        <p>Temperature: <?php echo $currentWeatherData['main']['temp']; ?>°C</p>
        <p>Humidity: <?php echo $currentWeatherData['main']['humidity']; ?>%</p>
        <p>Wind Speed: <?php echo $currentWeatherData['wind']['speed']; ?> m/s</p>

        <div class="mt-4">
          <h6>Next 6 Hours Forecast</h6>
          <?php foreach ($nextSixHoursForecast as $f): ?>
            <div class="forecast-item">
              <p><strong><?php echo date('D H:i', strtotime($f['dt_txt'])); ?></strong></p>
              <p class="emoji"><?php echo $weatherEmojis[$f['weather'][0]['main']] ?? '🌈'; ?></p>
              <p><?php echo $f['weather'][0]['description']; ?></p>
              <p><?php echo $f['main']['temp']; ?>°C</p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- News -->
      <div class="card news-card">
  <h5 class="mb-3">Top News Headlines</h5>
  <div class="news-filter mb-3">
    <select id="newsCategory" class="form-select" onchange="loadNews()">
      <option value="general" selected>General</option>
      <option value="sports">Sports</option>
      <option value="technology">Technology</option>
      <option value="business">Business</option>
      <option value="entertainment">Entertainment</option>
    </select>
  </div>
  <ul id="newsList">
    <?php foreach ($newsArticles as $article): ?>
      <li class="news-item d-flex mb-4">
        <?php if (!empty($article['image'])): ?>
          <img src="<?php echo $article['image']; ?>" alt="News Image" class="news-image img-thumbnail me-3" style="max-width: 150px; max-height: 150px; object-fit: cover; border-radius: 8px;">
        <?php endif; ?>
        <div>
          <a href="<?php echo $article['url']; ?>" target="_blank" class="news-title fs-5 text-decoration-none" style="color: #0a3d62; font-weight: 600;"><?php echo htmlspecialchars($article['title']); ?></a>
          <p class="news-description" style="font-size: 0.9rem; color: #333;"><?php echo htmlspecialchars($article['description']); ?></p>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

<script>
  function loadNews() {
    const category = document.getElementById('newsCategory').value;
    const url = `https://gnews.io/api/v4/top-headlines?token=<?php echo $gnewsApiKey; ?>&lang=en&country=in&topic=${category}`;
    
    fetch(url)
      .then(response => response.json())
      .then(data => {
        const newsList = document.getElementById('newsList');
        newsList.innerHTML = '';

        data.articles.forEach(article => {
          const listItem = document.createElement('li');
          listItem.classList.add('news-item', 'd-flex', 'mb-4');
          
          const img = article.image ? `<img src="${article.image}" alt="News Image" class="news-image img-thumbnail me-3" style="max-width: 150px; max-height: 150px; object-fit: cover; border-radius: 8px;">` : '';
          const title = `<a href="${article.url}" target="_blank" class="news-title fs-5 text-decoration-none" style="color: #0a3d62; font-weight: 600;">${article.title}</a>`;
          const description = `<p class="news-description" style="font-size: 0.9rem; color: #333;">${article.description}</p>`;
          
          listItem.innerHTML = img + `<div>${title}${description}</div>`;
          newsList.appendChild(listItem);
        });
      })
      .catch(error => console.error('Error loading news:', error));
  }
</script>

<style>
  .news-image {
    max-width: 150px;
    max-height: 150px;
    object-fit: cover;
    border-radius: 8px;
  }
  .news-item:hover .news-image {
    transform: scale(1.1);
    transition: 0.3s ease-in-out;
  }
  .news-title:hover {
    color: #ff6347;
  }
  .news-filter select {
    width: 200px;
  }
</style>
</div> <!-- .right-column -->
</div> <!-- .row -->
</div> <!-- .container -->

<script>
  // Like post
  document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const postId = this.getAttribute('data-post-id');
      fetch('like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}`
      })
      .then(response => response.text())
      .then(() => location.reload());
    });
  });

  // Submit comment
  document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const postId = this.getAttribute('data-post-id');
      const input = this.querySelector('input[name="comment_text"]');
      const comment = input.value.trim();

      if (comment === "") return; // don't submit empty comment

      fetch('comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&comment_text=${encodeURIComponent(comment)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const newCommentHTML = `
            <div class="comment">
              <strong>${data.user_name}:</strong> ${data.comment_text}
            </div>
          `;
          const commentsContainer = this.closest('.post').querySelector('.comments-container');
          commentsContainer.innerHTML += newCommentHTML; // Add the new comment to the post
          input.value = ""; // Clear the input field
        } else {
          alert('Error: ' + data.error);
        }
      })
      .catch(err => console.error('Error:', err));
    });
  });
</script>

</body>
</html>


