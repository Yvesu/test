import React, { Component } from 'react'

//平台看板
class Platform extends Component{
  render(){
    return(
      <div>
        <div className="platform_box">
            <h2>123123.21 GB</h2>
            <p>
              <span>模板：21%</span>
              <span>片段：29%</span>
              <span>特效：25%</span>
              <span>贴纸：25%</span>
            </p>
        </div>
        <div className="platform_statistics_box">
            <h2> 模板
              <span>1223.12 GB</span>
            </h2>
            <div className="statistics_single">
                <div className="single_left">
                  <div>
                      <p>官方：<em>12312312</em></p>
                      <p>
                        <span>日: <em>811</em>  </span>
                        <span>周: <em>324123</em>  </span>
                        <span>月: <em>324123</em>  </span>
                      </p>
                  </div>
                  <div>
                    <p>创作者：<em>2138971321</em></p>
                    <p>
                      <span>日: <em>324123</em> </span>
                      <span>周: <em>324123</em>  </span>
                      <span>月: <em>324123</em>  </span>
                    </p>
                  </div>
                </div>
                <div className="single_right">
                  <p>
                    <span>日: <em>0.82 GB</em>  </span>
                    <span>周: <em>8.9 GB</em>  </span>
                    <span>月: <em>26.5 GB</em>  </span>
                  </p>
                </div>
            </div>
        </div>
        <div className="platform_statistics_box">
            <h2> 片段
              <span>1223.12 GB</span>
            </h2>
            <div className="statistics_single">
                <div className="single_left">
                  <div>
                      <p>官方：<em>12312312</em></p>
                      <p>
                        <span>日: <em>811</em>  </span>
                        <span>周: <em>324123</em>  </span>
                        <span>月: <em>324123</em>  </span>
                      </p>
                  </div>
                  <div>
                    <p>创作者：<em>2138971321</em></p>
                    <p>
                      <span>日: <em>324123</em> </span>
                      <span>周: <em>324123</em>  </span>
                      <span>月: <em>324123</em>  </span>
                    </p>
                  </div>
                </div>
                <div className="single_right">
                  <p>
                    <span>日: <em>0.82 GB</em>  </span>
                    <span>周: <em>8.9 GB</em>  </span>
                    <span>月: <em>26.5 GB</em>  </span>
                  </p>
                </div>
            </div>
        </div>

      </div>
    )
  }
}

export default Platform
