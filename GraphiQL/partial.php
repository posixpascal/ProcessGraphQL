<div id="graphiql">Loading...</div>
<script>
  // set the processwire config variables
  var config = <?= json_encode($config->js()) ?>;

  /**
   * The below code is a copy from
   * https://github.com/graphql/graphiql/blob/master/packages/graphiql-examples/cdn/index.html
   * The only thing that's changed is the graphiql request url.
   */
  // Parse the search string to get url parameters.
  var search = window.location.search;
  var parameters = {};
  search.substr(1).split('&').forEach(function (entry) {
    var eq = entry.indexOf('=');
    if (eq >= 0) {
      parameters[decodeURIComponent(entry.slice(0, eq))] =
        decodeURIComponent(entry.slice(eq + 1));
    }
  });
  // If variables was provided, try to format it.
  if (parameters.variables) {
    try {
      parameters.variables =
        JSON.stringify(JSON.parse(parameters.variables), null, 2);
    } catch (e) {
      // Do nothing, we want to display the invalid JSON as a string, rather
      // than present an error.
    }
  }
  // When the query and variables string is edited, update the URL bar so
  // that it can be easily shared.
  function onEditQuery(newQuery) {
    parameters.query = newQuery;
    updateURL();
  }
  function onEditVariables(newVariables) {
    parameters.variables = newVariables;
    updateURL();
  }
  function onEditOperationName(newOperationName) {
    parameters.operationName = newOperationName;
    updateURL();
  }
  function updateURL() {
    var newSearch = '?' + Object.keys(parameters).filter(function (key) {
      return Boolean(parameters[key]);
    }).map(function (key) {
      return encodeURIComponent(key) + '=' +
        encodeURIComponent(parameters[key]);
    }).join('&');
    history.replaceState(null, null, newSearch);
  }
  // Defines a GraphQL fetcher using the fetch API. You're not required to
  // use fetch, and could instead implement graphQLFetcher however you like,
  // as long as it returns a Promise or Observable.
  function graphQLFetcher(graphQLParams) {
    // When working locally, the example expects a GraphQL server at the path /graphql.
    // In a PR preview, it connects to the Star Wars API externally.
    // Change this to point wherever you host your GraphQL server.
    return fetch(config.ProcessGraphQL.GraphQLServerUrl, {
      method: 'post',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(graphQLParams),
      credentials: 'include',
    }).then(function (response) {
      return response.text();
    }).then(function (responseBody) {
      try {
        return JSON.parse(responseBody);
      } catch (error) {
        return responseBody;
      }
    });
  }
  // Render <GraphiQL /> into the body.
  // See the README in the top level of this module to learn more about
  // how you can customize GraphiQL by providing different values or
  // additional child elements.
  ReactDOM.render(
    React.createElement(GraphiQL, {
      fetcher: graphQLFetcher,
      query: parameters.query,
      variables: parameters.variables,
      operationName: parameters.operationName,
      onEditQuery: onEditQuery,
      onEditVariables: onEditVariables,
      defaultVariableEditorOpen: true,
      onEditOperationName: onEditOperationName
    }),
    document.getElementById('graphiql')
  );
</script>